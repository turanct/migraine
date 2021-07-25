<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
final class Config
{
    private $workingDirectory;
    private $migrationsDirectory;
    private $logStrategy;
    private $groups;

    /**
     * @param string $workingDirectory
     * @param string $migrationsDirectory
     * @param Group[] $groups
     */
    public function __construct(
        string $workingDirectory,
        string $migrationsDirectory,
        LogStrategy $logStrategy,
        array $groups
    ) {
        $this->workingDirectory = $workingDirectory;
        $this->migrationsDirectory = $migrationsDirectory;
        $this->logStrategy = $logStrategy;
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

    public function getLogStrategy(): LogStrategy
    {
        return $this->logStrategy;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
