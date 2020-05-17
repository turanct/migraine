<?php

namespace Turanct\Migrations;

final class LogsInMemory implements Logs
{
    /**
     * @var Event[]
     */
    private $events = [];

    public function append(Event $event): void
    {
        $this->events[] = $event;
    }

    public function migrationWasExecuted(string $connectionString, string $migration): bool
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

    public function getAll(): array
    {
        return $this->events;
    }
}
