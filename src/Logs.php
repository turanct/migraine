<?php

namespace Turanct\Migrations;

interface Logs
{
    public function append(Event $event): void;

    /**
     * @return Event[]
     */
    public function getAll(): array;
}
