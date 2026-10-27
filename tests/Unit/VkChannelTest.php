<?php

namespace Tests\Unit;

use App\Models\Appliance;
use App\Models\Question;
use App\Models\User;
use App\Services\Announcements\Channels\VkChannel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Запись на стене VK-группы: текст, вложение со ссылкой, правка, удаление; ошибки VK и секреты. */
class VkChannelTest extends TestCase
{
    private function configure(array $over = []): void
    {
        config(['services.vk_group' => $over + ['token' => 'vk1.a.SECRET-VK-TOKEN', 'group_id' => '123456', 'version' => '5.199']]);
    }

    private function question(array $attributes = []): Question
    {
        return (new Question())->forceFill($attributes + [
            'id' => 7,
            'slug' => 'stiralka-ne-slivaet',
            'title' => 'Стиралка не сливает воду',
            'description' => 'Вода остаётся в баке, насос гудит.',
            'model' => 'Beko WKL 13540 K',
            'error_code' => 'E03',
        ])
            ->setRelation('appliance', (new Appliance())->forceFill(['title' => 'Стиральная машина']))
            ->setRelation('user', (new User())->forceFill(['name' => 'Иван']));
    }

    public function test_channel_is_enabled_only_with_token_and_group(): void
    {
        $this->configure();
        $this->assertTrue((new VkChannel())->enabled());

        $this->configure(['token' => null]);
        $this->assertFalse((new VkChannel())->enabled());

        $this->configure(['group_id' => '']);
        $this->assertFalse((new VkChannel())->enabled());
    }

    public function test_text_is_plain_with_title_facts_author_and_excerpt(): void
    {
        $text = (new VkChannel())->text($this->question(['description' => 'Вода <b>остаётся</b> в баке <img src="/x.jpg">']));

        $this->assertStringStartsWith('Стиралка не сливает воду', $text);
        $this->assertStringContainsString("Стиральная машина · Beko WKL 13540 K · ошибка E03\nСпрашивает: Иван", $text);
        $this->assertStringContainsString('Вода остаётся в баке', $text);
        $this->assertStringNotContainsString('<', $text);
        $this->assertStringNotContainsString('http', $text);
    }

    public function test_solved_question_is_marked(): void
    {
        $this->assertStringStartsWith('✅ Вопрос решён', (new VkChannel())->text($this->question(['solved_at' => now()])));
    }

    public function test_send_posts_to_the_group_wall_with_the_link_as_attachment(): void
    {
        $this->configure();
        Http::fake(['api.vk.com/*' => Http::response(['response' => ['post_id' => 987]])]);
        $channel = new VkChannel();
        $question = $this->question();

        $id = $channel->send($question);

        $this->assertSame('987', $id);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.vk.com/method/wall.post'
            && $request->hasHeader('Authorization', 'Bearer vk1.a.SECRET-VK-TOKEN')
            && $request['owner_id'] === -123456
            && $request['from_group'] === 1
            && $request['v'] === '5.199'
            && $request['attachments'] === $channel->url($question)
            && str_contains($request['message'], 'Стиралка не сливает воду')
            && ! str_contains($request->url(), 'SECRET'));
    }

    public function test_update_edits_the_post(): void
    {
        $this->configure();
        Http::fake(['api.vk.com/*' => Http::response(['response' => 1])]);

        (new VkChannel())->update($this->question(['title' => 'Новый заголовок вопроса про насос']), '987');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.vk.com/method/wall.edit'
            && $request['post_id'] === 987 && $request['owner_id'] === -123456
            && str_contains($request['message'], 'Новый заголовок вопроса про насос'));
    }

    public function test_retract_deletes_the_post(): void
    {
        $this->configure();
        Http::fake(['api.vk.com/*' => Http::response(['response' => 1])]);

        (new VkChannel())->retract('987');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.vk.com/method/wall.delete' && $request['post_id'] === 987);
    }

    public function test_error_inside_a_200_response_throws(): void
    {
        $this->configure();
        Http::fake(['api.vk.com/*' => Http::response(['error' => ['error_code' => 214, 'error_msg' => 'Access to adding post denied: you can only add 50 posts a day']])]);

        try {
            (new VkChannel())->send($this->question());
            $this->fail('Исключение не брошено');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('VK:', $e->getMessage());
            $this->assertStringContainsString('214', $e->getMessage());
        }
    }

    public function test_errors_do_not_leak_the_token(): void
    {
        $this->configure();
        Http::fake(fn () => throw new ConnectionException('cURL error 28: https://api.vk.com/method/wall.post?access_token=vk1.a.SECRET-VK-TOKEN timed out, Authorization: Bearer vk1.a.SECRET-VK-TOKEN'));

        try {
            (new VkChannel())->send($this->question());
            $this->fail('Исключение не брошено');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('SECRET-VK-TOKEN', $e->getMessage());
        }
    }
}
