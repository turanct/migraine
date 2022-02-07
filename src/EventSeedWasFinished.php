<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
interface EventSeedWasFinished extends Event
{
    public function getConnectionString(): string;

    public function getSeed(): string;
}
