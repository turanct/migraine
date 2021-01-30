<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
interface EventMigrationWasFinished extends Event
{
    public function getConnectionString(): string;

    public function getMigration(): string;
}
