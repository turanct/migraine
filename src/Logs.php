<?php

namespace Turanct\Migraine;

interface Logs
{
    public function append(Event $event): void;

    public function migrationWasExecuted(string $connectionString, string $migration): bool;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @return Event[]
     */
    public function getAll(): array;
}
