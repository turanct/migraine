<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class LogsInMemoryTest extends TestCase
{
    public function testAppendedEventsGetReturnedInOrder(): void
    {
        $strategy = new LogStrategyInMemory();
        $logs = new LogsInMemory();

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
        $strategy = new LogStrategyInMemory();
        $logs = new LogsInMemory();

        $migration = new EventMigrationWasExecuted(
            'connection',
            'migration',
            new \DateTimeImmutable('2020-05-01 23:59:59')
        );

        $logs->append($strategy, $migration);

        $this->assertEquals(true, $logs->migrationWasExecuted($strategy, 'connection', 'migration'));
        $this->assertEquals(false, $logs->migrationWasExecuted($strategy, 'anotherConnection', 'someMigration'));
    }
}
