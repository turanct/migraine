<?php

namespace Turanct\Migrations;

use PHPUnit\Framework\TestCase;

final class LogsJsonTest extends TestCase
{
    private $file = '/tmp/migration-log.json';

    public function setUp()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testAppendedEventsGetReturnedInOrder()
    {
        $logs = new LogsJson($this->file);

        $expected = [
            new EventMigrationWasExecuted('host', 'db', 'migration', new \DateTimeImmutable('2020-05-01 23:59:59')),
            new EventMigrationWasExecuted('host', 'db', 'migration', new \DateTimeImmutable('2020-05-02 10:00:00')),
            new EventMigrationWasExecuted('host', 'db', 'migration', new \DateTimeImmutable('2020-05-02 16:04:05')),
            new EventMigrationWasExecuted('host', 'db', 'migration', new \DateTimeImmutable('2020-05-03 12:12:12')),
        ];

        foreach ($expected as $event) {
            $logs->append($event);
        }

        $this->assertEquals($expected, $logs->getAll());
    }

    public function tearDown()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }
}
