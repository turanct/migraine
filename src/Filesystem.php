<?php

namespace Turanct\Migrations;

interface Filesystem
{
    public function touch(string $path): void;
}
