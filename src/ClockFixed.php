<?php

namespace Turanct\Migrations;

use DateTimeImmutable;

final class ClockFixed implements Clock
{
    /**
     * @var DateTimeImmutable
     */
    private $time;

    public function __construct(DateTimeImmutable $time)
    {
        $this->time = $time;
    }

    public function getTime(): DateTimeImmutable
    {
        return $this->time;
    }
}
