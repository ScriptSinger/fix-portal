<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

final class UserProfileTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    public function test_guest_can_read_public_profile_by_username(): void
    {
        $user = User::factory()->create([
            'name' => 'Александр Николаевич',
            'username' => 'alexand_nikol',
            'email' => 'private@example.test',
            'bio' => 'Мастер по технике',
            'location' => 'Уфа',
        ]);
        $question = $this->makeQuestion($user);
        $comment = $this->commentOn($question, $user);
        $reply = Reply::factory()->create(['user_id' => $user->id, 'comment_id' => $comment->id]);
        $question->forceFill(['solved_at' => now(), 'solution_type' => 'reply', 'solution_id' => $reply->id])->save();

        $this->get(route('users.profile', ['user' => $user->username]))
            ->assertOk()
            ->assertJsonPath('data.name', 'Александр Николаевич')
            ->assertJsonPath('data.username', 'alexand_nikol')
            ->assertJsonPath('data.bio', 'Мастер по технике')
            ->assertJsonPath('data.location', 'Уфа')
            ->assertJsonPath('data.statistics.questions', 1)
            ->assertJsonPath('data.statistics.answers', 2)
            ->assertJsonPath('data.statistics.solutions', 1)
            ->assertJsonMissingPath('data.email')
            ->assertJsonMissingPath('data.password');
    }

    public function test_unknown_username_returns_not_found(): void
    {
        $this->get('/users/not-existing-user/profile')->assertNotFound();
    }

    public function test_profile_endpoint_is_available_without_authentication(): void
    {
        $user = User::factory()->create(['username' => 'public_user']);

        $this->get(route('users.profile', ['user' => $user->username]))->assertOk();
    }
}
