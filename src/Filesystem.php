<?php

namespace Turanct\Migraine;

interface Filesystem
{
    public function touch(string $path): void;
}
