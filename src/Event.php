<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
interface Event
{
    public function toArray(): array;

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $array): Event;
}
