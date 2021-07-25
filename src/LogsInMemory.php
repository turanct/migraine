<?php

namespace Turanct\Migraine;

/**
 * @psalm-suppress UnusedClass
 */
final class LogsInMemory implements Logs
{
    /**
     * @var Event[]
     */
    private $events = [];

    public function acceptsStrategy(LogStrategy $strategy): bool
    {
        return $strategy instanceof LogStrategyInMemory;
    }

    public function append(LogStrategy $strategy, Event $event): void
    {
        $this->events[] = $event;
    }

    public function migrationWasExecuted(LogStrategy $strategy, string $connectionString, string $migration): bool
    {
        foreach ($this->events as $event) {
            $event = $event->toArray();

            if (
                $event['event'] === EventMigrationWasExecuted::class
                && $event['connectionString'] === $connectionString
                && $event['migration'] === $migration
            ) {
                return true;
            }
        }

        return false;
    }

    public function getAll(LogStrategy $strategy): array
    {
        return $this->events;
    }
}
