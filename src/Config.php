<?php

namespace Turanct\Migrations;

/**
 * @psalm-immutable
 */
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

    public function getMigrationsDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
