<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class SkipTest extends TestCase
{
    public function test_it_skips_a_single_migration()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $skip = new Skip($getConfig, $logs, $clock);

        $migrationName = 'single-migration.sql';
        $actualInfo = $skip->skipSingle(true, $migrationName);

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasSkipped(
                $this->connectionString(),
                $migrationName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT name FROM sqlite_master WHERE type="table"');
        $tables = $result->fetchAll();
        $this->assertEquals([], $tables);
    }

    public function test_it_dry_runs_a_single_migration()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $skip = new Skip($getConfig, $logs, $clock);

        $migrationName = 'single-migration.sql';
        $actualInfo = $skip->skipSingle(false, $migrationName);

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasSkipped(
                $this->connectionString(),
                $migrationName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $this->assertEquals(false, file_exists($this->dbFile()));
    }

    private function clearDatabase(): void
    {
        $dbFile = $this->dbFile();
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }
    }

    private function connectionString()
    {
        return "sqlite:{$this->dbFile()}";
    }

    private function dbFile()
    {
        return __DIR__ . '/test';
    }
}
