<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\AccessLogReader;
use Tests\TestCase;

final class AccessLogReaderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . '/fix-portal-access-log-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->directory);

        parent::tearDown();
    }

    public function test_large_log_is_bounded_and_keeps_the_newest_rows(): void
    {
        $lines = [];

        for ($i = 0; $i < 10000; $i++) {
            $lines[] = json_encode([
                'message' => 'access',
                'context' => [
                    'request_id' => 'request-' . $i,
                    'method' => 'GET',
                    'path' => '/questions/' . $i,
                    'status' => 200,
                    'duration_ms' => 10,
                    'ip' => '127.0.0.1',
                    'user_agent' => 'load-test',
                    'is_bot' => false,
                ],
                'datetime' => sprintf('2026-10-31T00:%02d:%02d.000000Z', intdiv($i, 60) % 60, $i % 60),
            ], JSON_THROW_ON_ERROR);
        }

        file_put_contents($this->directory . '/access-2026-10-31.log', implode("\n", $lines) . "\n");

        $rows = (new AccessLogReader($this->directory))->entries(['max_rows' => 100]);

        self::assertCount(100, $rows);
        self::assertSame('request-9999', $rows->last()['request_id']);
        self::assertSame('request-9900', $rows->first()['request_id']);
    }

    public function test_request_user_and_status_filters_are_applied_before_rendering(): void
    {
        $records = [
            ['request_id' => 'abc-1', 'user_id' => 42, 'status' => 200],
            ['request_id' => 'abc-2', 'user_id' => 7, 'status' => 403],
            ['request_id' => 'xyz-3', 'user_id' => 42, 'status' => 500],
        ];

        $lines = array_map(static function (array $record, int $i): string {
            return json_encode([
                'message' => 'access',
                'context' => $record + [
                    'method' => 'GET',
                    'path' => '/test',
                    'duration_ms' => 1,
                    'ip' => '127.0.0.1',
                ],
                'datetime' => '2026-10-31T00:00:' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . '.000000Z',
            ], JSON_THROW_ON_ERROR);
        }, $records, array_keys($records));

        file_put_contents($this->directory . '/access-2026-10-31.log', implode("\n", $lines) . "\n");

        $rows = (new AccessLogReader($this->directory))->entries([
            'request_id' => 'xyz',
            'user_id' => '42',
            'status' => '500',
        ]);

        self::assertCount(1, $rows);
        self::assertSame('xyz-3', $rows->first()['request_id']);
    }
}
