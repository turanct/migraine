<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class MigrateUpTest extends TestCase
{
    public function test_it_runs_a_single_migration()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $migrationName = 'single-migration.sql';
        $actualInfo = $migrateUp->migrateSingle(true, $migrationName);

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                $migrationName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT name FROM sqlite_master WHERE type="table"');
        $tables = $result->fetchAll();
        $this->assertEquals([['test', 'name' => 'test']], $tables);
    }

    public function test_it_dry_runs_a_single_migration()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $migrationName = 'single-migration.sql';
        $actualInfo = $migrateUp->migrateSingle(false, $migrationName);

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                $migrationName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $this->assertEquals(false, file_exists($this->dbFile()));
    }

    public function test_it_fails_a_single_migration_when_the_file_is_corrupt()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $migrationName = 'faulty-migration.sql';
        $actualInfo = $migrateUp->migrateSingle(true, $migrationName);

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->withError('SQLSTATE[HY000]: General error: 1 near "crate": syntax error');

        $this->assertEquals($expectedInfo, $actualInfo);
    }

    public function test_it_runs_all_migrations()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('all-migrations-test', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $actualInfo = $migrateUp->migrateUp(true, 'all-migrations-test');

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                'migration-1.sql',
                $time
            )
        );
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                'migration-2.sql',
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $expectedStructure = "CREATE TABLE `test` (\n    `id` varchar(255) NOT NULL\n, `name` varchar(255))";

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT `sql` FROM `sqlite_master` WHERE `name` = "test"');
        $tables = $result->fetchAll();
        $this->assertEquals($expectedStructure, $tables[0]['sql']);
    }

    public function test_it_dry_runs_all_migrations()
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            [new Group('all-migrations-test', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $actualInfo = $migrateUp->migrateUp(false, 'all-migrations-test');

        $expectedInfo = new CompletedMigrations();
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                'migration-1.sql',
                $time
            )
        );
        $expectedInfo->completed(
            new EventMigrationWasExecuted(
                $this->connectionString(),
                'migration-2.sql',
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $this->assertEquals(false, file_exists($this->dbFile()));
    }

    public function test_it_fails_when_the_group_directory_does_not_exist()
    {
        $this->expectException(MigrationsDirectoryNotFound::class);

        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures-that-dont-exist',
            [new Group('non-existing-group', [new Database($this->connectionString(), '', '')])]
        );

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);

        $migrateUp = new MigrateUp($config, $logs, $clock);

        $actualInfo = $migrateUp->migrateUp(true, 'non-existing-group');
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
