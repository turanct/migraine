<?php

namespace Turanct\Migraine;

use PDO;
use PDOException;

final class Skip
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logs
     */
    private $logs;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(Config $config, Logs $logs, Clock $clock)
    {
        $this->config = $config;
        $this->logs = $logs;
        $this->clock = $clock;
    }

    /**
     * @param bool $commit
     * @param string $migrationName
     *
     * @return CompletedMigrations
     */
    public function skipSingle(bool $commit, string $migrationName): CompletedMigrations
    {
        $completedMigrations = new CompletedMigrations();

        if (empty($migrationName)) {
            $completedMigrations->withError('Provide a valid migration name.');

            return $completedMigrations;
        }

        $groups = $this->config->getGroups();

        foreach ($groups as $group) {
            $migrationPath = "{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/";
            $migrationPath.= "{$group->getName()}/{$migrationName}";

            $file = new \SplFileInfo($migrationPath);
            if (!$file->isFile()) {
                continue;
            }

            $databases = $group->getDatabases();

            foreach ($databases as $database) {
                if ($this->logs->migrationWasExecuted($database->getConnectionString(), $file->getFilename())) {
                    continue;
                }

                try {
                    $migrationWasSkipped = new EventMigrationWasSkipped(
                        $database->getConnectionString(),
                        $file->getFilename(),
                        $this->clock->getTime()
                    );

                    if ($commit === true) {
                        $this->logs->append($migrationWasSkipped);
                    }

                    $completedMigrations->completed($migrationWasSkipped);
                } catch (QueryFailed $e) {
                    $completedMigrations->withError($e->getMessage());

                    return $completedMigrations;
                } catch (PDOException $e) {
                    $completedMigrations->withError($e->getMessage());

                    return $completedMigrations;
                }
            }
        }

        return $completedMigrations;
    }
}
