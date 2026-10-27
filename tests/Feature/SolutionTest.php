<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reputation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Решённые вопросы: кто и что может отметить, фильтры списка, уведомление и репутация. */
class SolutionTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function setup3(): array
    {
        $author = User::factory()->create();
        $helper = User::factory()->create();
        $question = $this->makeQuestion($author);

        return [$author, $helper, $question, $this->commentOn($question, $helper)];
    }

    public function test_author_marks_an_answer_as_the_solution_and_the_answerer_is_notified(): void
    {
        [$author, $helper, $question, $answer] = $this->setup3();

        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]), ['type' => 'comment', 'message' => $answer->id])
            ->assertRedirect(route('questions.show', ['question' => $question->slug]) . '#c-' . $answer->id);

        $question->refresh();
        $this->assertTrue($question->isSolved());
        $this->assertSame($answer->id, $question->solution()->id);
        $this->assertSame('solution', $helper->notifications()->first()->data['kind']);
    }

    public function test_the_page_shows_the_status_and_only_the_author_gets_the_buttons(): void
    {
        [$author, $helper, $question, $answer] = $this->setup3();
        $page = route('questions.show', ['question' => $question->slug]);

        $this->actingAs($author)->get($page)->assertSee('Это решение')->assertSee('Отметить решённым');
        $this->actingAs($helper)->get($page)->assertDontSee('Это решение')->assertDontSee('Отметить решённым');

        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]), ['type' => 'comment', 'message' => $answer->id]);
        $this->get($page)->assertSee('✓ Решено')->assertSee('✓ Решение')->assertSee('Снять отметку');
    }

    public function test_only_the_author_can_mark_and_only_messages_of_this_question(): void
    {
        [$author, $helper, $question, $answer] = $this->setup3();
        $store = route('questions.solution.store', ['id' => $question->id]);

        $this->actingAs($helper)->post($store, ['type' => 'comment', 'message' => $answer->id])->assertForbidden();

        $foreign = $this->commentOn($this->makeQuestion(User::factory()->create()), $helper);
        $this->actingAs($author)->post($store, ['type' => 'comment', 'message' => $foreign->id])->assertNotFound();

        $own = $this->commentOn($question, $author);
        $this->post($store, ['type' => 'comment', 'message' => $own->id])->assertForbidden();
        $this->assertFalse($question->refresh()->isSolved());
    }

    public function test_solution_can_be_changed_and_removed(): void
    {
        [$author, $helper, $question, $first] = $this->setup3();
        $second = $this->commentOn($question, User::factory()->create(), 'Другой вариант');
        $store = route('questions.solution.store', ['id' => $question->id]);

        $this->actingAs($author)->post($store, ['type' => 'comment', 'message' => $first->id]);
        $this->post($store, ['type' => 'comment', 'message' => $second->id]);
        $this->assertSame($second->id, $question->refresh()->solution()->id);

        $this->delete(route('questions.solution.destroy', ['id' => $question->id]))->assertRedirect();
        $this->assertFalse($question->refresh()->isSolved());
        $this->assertNull($question->solution());
    }

    public function test_marking_solved_without_choosing_an_answer(): void
    {
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);

        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]))->assertRedirect();

        $question->refresh();
        $this->assertTrue($question->isSolved());
        $this->assertNull($question->solution());
    }

    public function test_status_filter_in_the_list(): void
    {
        $author = User::factory()->create();
        $solved = $this->makeQuestion($author, ['title' => 'Решённый вопрос про сушку ' . uniqid()]);
        $solved->forceFill(['solved_at' => now()])->save();
        $open = $this->makeQuestion($author, ['title' => 'Открытый вопрос про сушку ' . uniqid()]);

        $this->get(route('questions.index', ['status' => 'solved']))->assertSee($solved->title)->assertDontSee($open->title);
        $this->get(route('questions.index', ['status' => 'open']))->assertSee($open->title)->assertDontSee($solved->title);
        $this->get(route('questions.index'))->assertSee($solved->title)->assertSee($open->title);
    }

    public function test_old_questions_are_not_counted_as_unsolved(): void
    {
        $old = $this->makeQuestion(User::factory()->create(), ['title' => 'Старый вопрос из архива ' . uniqid()]);
        $old->forceFill(['created_at' => '2020-01-01 00:00:00'])->saveQuietly();

        $this->get(route('questions.index', ['status' => 'open']))->assertDontSee($old->title);
    }

    public function test_reputation_counts_solutions_of_others_only(): void
    {
        [$author, $helper, $question, $answer] = $this->setup3();
        $this->assertSame(0, Reputation::countFor($helper->id));

        $question->forceFill(['solved_at' => now(), 'solution_type' => 'comment', 'solution_id' => $answer->id])->save();
        $this->assertSame(1, Reputation::countFor($helper->id));

        // решение, где отвечал сам автор вопроса, не в счёт (даже если оно попало в базу в обход формы)
        $own = $this->commentOn($question, $author);
        $question->forceFill(['solution_id' => $own->id])->save();
        $this->assertSame(0, Reputation::countFor($author->id));
    }

    public function test_ranks_and_progress(): void
    {
        $this->assertNull(Reputation::rank(0));
        $this->assertSame('Помощник', Reputation::rank(1));
        $this->assertSame('Помощник', Reputation::rank(2));
        $this->assertSame('Знаток', Reputation::rank(3));
        $this->assertSame('Эксперт', Reputation::rank(10));
        $this->assertSame(['Знаток', 1], Reputation::next(2));
        $this->assertNull(Reputation::next(10));
        $this->assertEqualsWithDelta(0.5, Reputation::progress(2), 0.001);
        $this->assertNull(Reputation::progress(10));
    }

    public function test_helpers_of_the_month_are_ordered_by_solutions(): void
    {
        $author = User::factory()->create();
        [$top, $other] = [User::factory()->create(), User::factory()->create()];
        foreach ([[$top, 2], [$other, 1]] as [$user, $n]) {
            foreach (range(1, $n) as $i) {
                $q = $this->makeQuestion($author);
                $a = $this->commentOn($q, $user);
                $q->forceFill(['solved_at' => now(), 'solution_type' => 'comment', 'solution_id' => $a->id])->save();
            }
        }

        $ids = Reputation::top(5, now()->subDays(30))->pluck('user.id')->all();
        $this->assertSame([$top->id, $other->id], array_slice($ids, 0, 2));
    }
}
