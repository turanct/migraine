<?php

namespace Turanct\Migrations;

final class Config
{
    private $directory;
    private $groups;

    /**
     * @param string $directory
     * @param Group[] $groups
     */
    public function __construct(string $directory, array $groups)
    {
        $this->directory = $directory;
        $this->groups = $groups;
    }
}
