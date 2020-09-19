<?php

namespace Turanct\Migraine;

use DateTimeImmutable;

final class ClockNow implements Clock
{
    public function getTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
