<?php

namespace Turanct\Migraine;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LogsDispatchTest extends TestCase
{
    /**
     * @dataProvider loggerIsRegistered
     */
    public function test_it_passes_through_the_calls_if_appropriate_logger_is_registered(array $loggers)
    {
        $dispatcher = new LogsDispatch($loggers);

        $strategy = new LogStrategyInMemory();

        $connectionString = 'connection';
        $migration = 'migration';
        $eventMigrationWasExecuted = new EventMigrationWasExecuted($connectionString, $migration, new DateTimeImmutable('now'));

        $dispatcher->append($strategy, $eventMigrationWasExecuted);

        $this->assertTrue($dispatcher->migrationWasExecuted($strategy, $connectionString, $migration));
        $this->assertEquals([$eventMigrationWasExecuted], $dispatcher->getAll($strategy));
    }

    public function loggerIsRegistered()
    {
        return [
            'one logger registered, the one we need' => [[new LogsInMemory()]],
            'multiple loggers registered, the one we need' => [[new LogsJson(), new LogsInMemory()]],
        ];
    }

    /**
     * @dataProvider loggerIsNotRegistered
     */
    public function test_it_noops_when_appropriate_logger_is_not_registered(array $loggers)
    {
        $dispatcher = new LogsDispatch($loggers);

        $strategy = new LogStrategyInMemory();

        $connectionString = 'connection';
        $migration = 'migration';
        $eventMigrationWasExecuted = new EventMigrationWasExecuted($connectionString, $migration, new DateTimeImmutable('now'));

        $dispatcher->append($strategy, $eventMigrationWasExecuted);

        $this->assertFalse($dispatcher->migrationWasExecuted($strategy, $connectionString, $migration));
        $this->assertEquals([], $dispatcher->getAll($strategy));
    }

    public function loggerIsNotRegistered()
    {
        return [
            'no loggers registered' => [[]],
            'one logger registered, one we do not need' => [[new LogsJson()]],
            'multiple loggers registered, the one we need' => [[new LogsJson(), new LogsJson()]],
        ];
    }
}
