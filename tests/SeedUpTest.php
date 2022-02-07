<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

class SeedUpTest extends TestCase
{
    public function test_it_dry_runs_a_single_seed(): void
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            new LogStrategyJson('logs.json'),
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);
        $this->createSingleMigration($getConfig, $logs, $clock);

        $seedUp = new SeedUp($getConfig, $logs, $clock);
        $seedName = 'single-migration.sql';
        $actualInfo = $seedUp->seed(false, $seedName);

        $expectedInfo = new CompletedSeeds();
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                $seedName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);
    }

    public function test_it_runs_a_single_seed(): void
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            new LogStrategyJson('logs.json'),
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);
        $this->createSingleMigration($getConfig, $logs, $clock);

        $seedUp = new SeedUp($getConfig, $logs, $clock);
        $seedName = 'single-seed.sql';
        $actualInfo = $seedUp->seed(true, "seeds/{$seedName}");

        $expectedInfo = new CompletedSeeds();
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                $seedName,
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT `id` FROM `test`');

        $this->assertEquals('This is an id', $result->fetchColumn(0));
    }

    public function test_it_fails_a_single_seed_when_the_file_is_corrupt(): void
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            new LogStrategyJson('logs.json'),
            [new Group('single-migration-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);
        $this->createSingleMigration($getConfig, $logs, $clock);

        $seedUp = new SeedUp($getConfig, $logs, $clock);
        $seedName = 'faulty-seed.sql';
        $actualInfo = $seedUp->seed(true, "seeds/{$seedName}");

        $expectedInfo = new CompletedSeeds();
        $expectedInfo->withError('SQLSTATE[HY000]: General error: 1 near "INSRT": syntax error');

        $this->assertEquals($expectedInfo, $actualInfo);
    }

    public function test_it_dry_runs_all_seeds(): void
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            new LogStrategyJson('logs.json'),
            [new Group('all-migrations-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);
        $this->createMigrations($getConfig, $logs, $clock);

        $seedUp = new SeedUp($getConfig, $logs, $clock);
        $actualInfo = $seedUp->seedUp(false, 'all-migrations-test');

        $expectedInfo = new CompletedSeeds();
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                'seed-1.sql',
                $time
            )
        );
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                'seed-2.sql',
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT * FROM `test`');
        $response = $result->fetchAll();

        $this->assertEmpty($response);
    }

    public function test_it_runs_all_seeds(): void
    {
        $this->clearDatabase();

        $config = new Config(
            __DIR__,
            'fixtures',
            new LogStrategyJson('logs.json'),
            [new Group('all-migrations-test', [new Database($this->connectionString(), '', '')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        $logs = new LogsInMemory();

        $time = new \DateTimeImmutable('now');
        $clock = new ClockFixed($time);
        $this->createMigrations($getConfig, $logs, $clock);

        $seedUp = new SeedUp($getConfig, $logs, $clock);
        $actualInfo = $seedUp->seedUp(true, 'all-migrations-test');

        $expectedInfo = new CompletedSeeds();
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                'seed-1.sql',
                $time
            )
        );
        $expectedInfo->completed(
            new EventSeedWasExecuted(
                $this->connectionString(),
                'seed-2.sql',
                $time
            )
        );

        $this->assertEquals($expectedInfo, $actualInfo);

        $realDBConnection = new \PDO($this->connectionString());
        $result = $realDBConnection->query('SELECT * FROM `test`');
        $response = $result->fetchAll();

        $this->assertEquals('This is an id', $response[0]['id']);
        $this->assertEquals('This is a name', $response[0]['name']);
    }

    private function createSingleMigration(GetConfig $getConfig, Logs $logs, Clock $clock)
    {
        $migrateUp = new MigrateUp($getConfig, $logs, $clock);
        $migrateUp->migrateSingle(true, 'single-migration.sql');
    }

    private function createMigrations(GetConfig $getConfig, Logs $logs, Clock $clock)
    {
        $migrateUp = new MigrateUp($getConfig, $logs, $clock);
        $migrateUp->migrateUp(true, 'all-migrations-test');
    }

    private function clearDatabase(): void
    {
        $dbFile = $this->dbFile();
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }
    }

    private function connectionString(): string
    {
        return "sqlite:{$this->dbFile()}";
    }

    private function dbFile(): string
    {
        return __DIR__ . '/test';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearDatabase();
    }
}
