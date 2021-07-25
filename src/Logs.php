<?php

namespace Turanct\Migraine;

interface Logs
{
    public function acceptsStrategy(LogStrategy $strategy): bool;

    public function append(LogStrategy $strategy, Event $event): void;

    public function migrationWasExecuted(LogStrategy $strategy, string $connectionString, string $migration): bool;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @return Event[]
     */
    public function getAll(LogStrategy $strategy): array;
}
