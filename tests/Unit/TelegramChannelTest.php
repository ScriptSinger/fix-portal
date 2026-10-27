<?php

namespace Tests\Unit;

use App\Models\Appliance;
use App\Models\Question;
use App\Services\Announcements\Channels\TelegramChannel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Сообщение в Telegram и отправка через прокси: текст безопасен для HTML-режима, секреты не попадают в ошибки. */
class TelegramChannelTest extends TestCase
{
    private function configure(array $over = []): void
    {
        config(['services.telegram' => $over + [
            'bot_token' => '123456:SECRET-TOKEN',
            'chat_id' => '-5494489427',
            'proxy' => 'http://fixportal:proxy-pass@194.36.177.104:28417',
            'api_url' => 'https://api.telegram.org',
        ]]);
    }

    private function question(array $attributes = []): Question
    {
        $question = (new Question())->forceFill($attributes + [
            'id' => 7,
            'slug' => 'stiralka-ne-slivaet',
            'title' => 'Стиралка не сливает воду',
            'description' => 'Вода остаётся в баке, насос гудит.',
            'model' => 'Beko WKL 13540 K',
            'error_code' => 'E03',
        ]);

        return $question->setRelation('appliance', (new Appliance())->forceFill(['title' => 'Стиральная машина']));
    }

    public function test_text_has_title_facts_excerpt_and_link(): void
    {
        $text = (new TelegramChannel())->text($this->question());

        $this->assertStringContainsString('<b>Стиралка не сливает воду</b>', $text);
        $this->assertStringContainsString('Стиральная машина · Beko WKL 13540 K · ошибка E03', $text);
        $this->assertStringContainsString('Вода остаётся в баке, насос гудит.', $text);
        $this->assertStringContainsString(route('questions.show', ['question' => 'stiralka-ne-slivaet']), $text);
    }

    public function test_user_text_cannot_inject_html(): void
    {
        $question = $this->question([
            'title' => 'Не греет <a href="https://spam.example">перейди</a> & <b>',
            'description' => 'Текст &lt;script&gt;alert(1)&lt;/script&gt; и <img src="/x.jpg"> фото',
        ]);

        $text = (new TelegramChannel())->text($question);

        $this->assertStringNotContainsString('<a href', $text);
        $this->assertStringNotContainsString('<script', $text);
        $this->assertStringNotContainsString('<img', $text);
        $this->assertStringContainsString('&lt;a href=&quot;https://spam.example&quot;&gt;', $text);
        // от разметки Telegram остаётся только наш заголовок в <b>
        $this->assertSame(1, substr_count($text, '<b>'));
    }

    public function test_long_description_is_cut(): void
    {
        $text = (new TelegramChannel())->text($this->question(['description' => str_repeat('слово ', 500)]));

        $this->assertLessThan(1000, mb_strlen($text));
    }

    public function test_channel_is_enabled_only_with_token_and_chat(): void
    {
        $this->configure();
        $this->assertTrue((new TelegramChannel())->enabled());

        $this->configure(['bot_token' => null]);
        $this->assertFalse((new TelegramChannel())->enabled());

        $this->configure(['chat_id' => '']);
        $this->assertFalse((new TelegramChannel())->enabled());
    }

    public function test_send_posts_to_the_chat_and_returns_message_id(): void
    {
        $this->configure();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 42]])]);

        $id = (new TelegramChannel())->send($this->question());

        $this->assertSame('42', $id);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.telegram.org/bot123456:SECRET-TOKEN/sendMessage'
                && $request['chat_id'] === '-5494489427'
                && $request['parse_mode'] === 'HTML'
                && str_contains($request['text'], 'Стиралка не сливает воду');
        });
    }

    public function test_api_url_can_point_to_a_relay(): void
    {
        $this->configure(['api_url' => 'https://relay.example/tg/']);
        Http::fake(['relay.example/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);

        (new TelegramChannel())->send($this->question());

        Http::assertSent(fn (Request $request) => $request->url() === 'https://relay.example/tg/bot123456:SECRET-TOKEN/sendMessage');
    }

    public function test_errors_do_not_leak_token_or_proxy_password(): void
    {
        $this->configure();
        Http::fake(fn () => throw new ConnectionException(
            'cURL error 56: proxy http://fixportal:proxy-pass@194.36.177.104:28417 refused https://api.telegram.org/bot123456:SECRET-TOKEN/sendMessage'
        ));

        try {
            (new TelegramChannel())->send($this->question());
            $this->fail('Исключение не брошено');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('SECRET-TOKEN', $e->getMessage());
            $this->assertStringNotContainsString('proxy-pass', $e->getMessage());
            $this->assertStringContainsString('Telegram:', $e->getMessage());
        }
    }

    public function test_telegram_error_response_throws_without_token(): void
    {
        $this->configure();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Forbidden: bot was kicked'], 403)]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Telegram:');

        (new TelegramChannel())->send($this->question());
    }
}
