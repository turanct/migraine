<?php

namespace Turanct\Migraine;

final class CompletedMigrations
{
    /**
     * @var EventMigrationWasExecuted[]
     */
    private $events = [];

    /**
     * @var string
     */
    private $error = '';

    public function completed(EventMigrationWasExecuted $event): void
    {
        $this->events[] = $event;
    }

    public function withError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return EventMigrationWasExecuted[]
     */
    public function getList(): array
    {
        return $this->events;
    }

    public function failed(): bool
    {
        return !empty($this->error);
    }

    public function getError(): string
    {
        return $this->error;
    }
}
