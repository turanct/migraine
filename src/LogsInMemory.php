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

    public function getAll(): array
    {
        return $this->events;
    }
}
