<?php

namespace Turanct\Migrations;

use DateTimeImmutable;

interface Clock
{
    public function getTime(): DateTimeImmutable;
}
