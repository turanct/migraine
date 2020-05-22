<?php

namespace Turanct\Migrations;

final class CompletedMigrations
{
    /**
     * @var EventMigrationWasExecuted[]
     */
    private $events = [];

    public function completed(EventMigrationWasExecuted $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return EventMigrationWasExecuted[]
     */
    public function getList(): array
    {
        return $this->events;
    }
}
