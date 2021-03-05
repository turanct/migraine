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
        $logs = new LogsJson($this->file);

        $expected = [
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-01 23:59:59')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-02 10:00:00')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-02 16:04:05')),
            new EventMigrationWasExecuted('connection', 'migration', new \DateTimeImmutable('2020-05-03 12:12:12')),
        ];

        foreach ($expected as $event) {
            $logs->append($event);
        }

        $this->assertEquals($expected, $logs->getAll());
    }

    public function testExecutedMigrationsAreRecognisedAsSuch()
    {
        $logs = new LogsJson($this->file);

        $migration = new EventMigrationWasExecuted(
            'connection',
            'migration',
            new \DateTimeImmutable('2020-05-01 23:59:59')
        );

        $logs->append($migration);

        $this->assertEquals(true, $logs->migrationWasExecuted('connection', 'migration'));
        $this->assertEquals(false, $logs->migrationWasExecuted('anotherConnection', 'someMigration'));
    }

    public function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }
}
