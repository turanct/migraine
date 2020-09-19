<?php

namespace Turanct\Migraine;

use DateTimeImmutable;

interface Clock
{
    public function getTime(): DateTimeImmutable;
}
