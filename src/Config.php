<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
final class Config
{
    private $workingDirectory;
    private $migrationsDirectory;
    private $groups;

    /**
     * @param string $workingDirectory
     * @param string $migrationsDirectory
     * @param Group[] $groups
     */
    public function __construct(string $workingDirectory, string $migrationsDirectory, array $groups)
    {
        $this->workingDirectory = $workingDirectory;
        $this->migrationsDirectory = $migrationsDirectory;
        $this->groups = $groups;
    }

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function getMigrationsDirectory(): string
    {
        return $this->migrationsDirectory;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
