<?php

namespace Tests\Unit;

use App\Services\Backup\DatabaseBackup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . '/db-backup-test-' . uniqid();
        File::ensureDirectoryExists($this->directory);

        config([
            'backup.path' => $this->directory,
            'backup.cloud_prefix' => 'db',
            'filesystems.disks.backups.bucket' => 'test-bucket',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    private function gz(string $name, string $content, ?int $daysAgo = null): string
    {
        $path = $this->directory . '/' . $name;
        file_put_contents($path, gzencode($content));

        if ($daysAgo !== null) {
            touch($path, now()->subDays($daysAgo)->getTimestamp());
        }

        return $path;
    }

    public function test_complete_dump_is_recognized(): void
    {
        $backup = new DatabaseBackup();

        $ok = $this->gz('ok.sql.gz', "CREATE TABLE t (id int);\n-- Dump completed on 2026-10-25 02:30:00\n");
        $truncated = $this->gz('cut.sql.gz', "CREATE TABLE t (id int);\nINSERT INTO t VALUES (1);\n");

        $this->assertTrue($backup->isComplete($ok));
        $this->assertFalse($backup->isComplete($truncated));
    }

    public function test_corrupted_archive_is_not_complete(): void
    {
        $path = $this->directory . '/broken.sql.gz';
        file_put_contents($path, 'это не gzip');

        $this->assertFalse((new DatabaseBackup())->isComplete($path));
    }

    public function test_prune_removes_old_copies_but_never_the_newest(): void
    {
        $this->gz('db-new.sql.gz', 'x', 0);
        $this->gz('db-week.sql.gz', 'x', 8);
        $this->gz('db-old.sql.gz', 'x', 20);

        $deleted = (new DatabaseBackup())->prune(7);

        $this->assertEqualsCanonicalizing(['db-week.sql.gz', 'db-old.sql.gz'], array_map('basename', $deleted));
        $this->assertFileExists($this->directory . '/db-new.sql.gz');
        $this->assertFileDoesNotExist($this->directory . '/db-old.sql.gz');
    }

    public function test_the_only_copy_is_kept_even_when_it_is_old(): void
    {
        $this->gz('db-only.sql.gz', 'x', 90);

        $this->assertSame([], (new DatabaseBackup())->prune(7));
        $this->assertFileExists($this->directory . '/db-only.sql.gz');
    }

    public function test_upload_puts_the_file_into_the_cloud_and_checks_size(): void
    {
        Storage::fake('backups');
        $file = $this->gz('db-2026-10-25.sql.gz', 'dump body');

        $path = (new DatabaseBackup())->upload($file);

        $this->assertSame('db/db-2026-10-25.sql.gz', $path);
        Storage::disk('backups')->assertExists($path);
        $this->assertSame(filesize($file), Storage::disk('backups')->size($path));
    }

    public function test_upload_requires_a_bucket(): void
    {
        config(['filesystems.disks.backups.bucket' => null]);
        $file = $this->gz('db.sql.gz', 'x');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BACKUP_S3_BUCKET');

        (new DatabaseBackup())->upload($file);
    }

    public function test_cloud_prune_keeps_the_newest_and_recent_copies(): void
    {
        Storage::fake('backups');
        $disk = Storage::disk('backups');

        foreach (['db/new.sql.gz' => 0, 'db/mid.sql.gz' => 10, 'db/old.sql.gz' => 45, 'db/older.sql.gz' => 90] as $path => $daysAgo) {
            $disk->put($path, 'x');
            touch($disk->path($path), now()->subDays($daysAgo)->getTimestamp());
        }

        $deleted = (new DatabaseBackup())->pruneCloud(30);

        $this->assertEqualsCanonicalizing(['db/old.sql.gz', 'db/older.sql.gz'], $deleted);
        $disk->assertExists('db/new.sql.gz');
        $disk->assertExists('db/mid.sql.gz');
    }

    public function test_only_mysql_connections_are_supported(): void
    {
        config(['database.connections.fake' => ['driver' => 'sqlite', 'database' => ':memory:']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MySQL');

        (new DatabaseBackup('fake'))->create();
    }
}
