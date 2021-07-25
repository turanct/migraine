<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class LogsJsonTest extends TestCase
{
    private $file = '/tmp/migration-log.json';

    public function setUp(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testAppendedEventsGetReturnedInOrder()
    {
        $strategy = new LogStrategyJson($this->file);
        $logs = new LogsJson();

        $expected = [
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-01 23:59:59')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-02 10:00:00')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-02 16:04:05')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-03 12:12:12')),
        ];

        foreach ($expected as $event) {
            $logs->append($strategy, $event);
        }

        $this->assertEquals($expected, $logs->getAll($strategy));
    }

    public function testExecutedMigrationsAreRecognisedAsSuch()
    {
        $strategy = new LogStrategyJson($this->file);
        $logs = new LogsJson();

        $migration = new EventMigrationWasExecuted(
            'connection',
            'migration',
            new \DateTimeImmutable('2020-05-01 23:59:59')
        );

        $logs->append($strategy, $migration);

        $this->assertEquals(true, $logs->migrationWasExecuted($strategy, 'connection', 'migration'));
        $this->assertEquals(false, $logs->migrationWasExecuted($strategy, 'anotherConnection', 'someMigration'));
    }

    public function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }
}
