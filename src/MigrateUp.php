<?php

namespace Turanct\Migrations;

use DateTimeImmutable;
use PDO;
use PDOException;

final class MigrateUp
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logs
     */
    private $logs;

    public function __construct(Config $config, Logs $logs)
    {
        $this->config = $config;
        $this->logs = $logs;
    }

    /**
     * @param bool $commit
     *
     * @throws MigrationsDirectoryNotFound
     *
     * @return CompletedMigrations
     */
    public function migrateUp(bool $commit = false): CompletedMigrations
    {
        $completedMigrations = new CompletedMigrations();

        $groups = $this->config->getGroups();

        foreach ($groups as $group) {
            $finder = new \Symfony\Component\Finder\Finder();
            try {
                /** @psalm-suppress TooManyArguments */
                $files = $finder
                    ->files()
                    ->in("{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/{$group->getName()}")
                    ->name('*.sql')
                    ->sortByName(true);
            } catch (\Symfony\Component\Finder\Exception\DirectoryNotFoundException $e) {
                throw new MigrationsDirectoryNotFound('', 0, $e);
            }

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($files as $file) {
                $migration = $file->getContents();

                $databases = $group->getDatabases();

                foreach ($databases as $database) {
                    if ($this->logs->migrationWasExecuted($database->getConnectionString(), $file->getFilename())) {
                        continue;
                    }

                    try {
                        if ($commit === true) {
                            $db = new PDO(
                                $database->getConnectionString(),
                                $database->getUser(),
                                $database->getPassword(),
                                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                            );

                            $result = $db->exec($migration);

                            if ($result === false) {
                                $errorInfo = $db->errorInfo();

                                throw QueryFailed::withMigrationData(
                                    (string) $errorInfo[2],
                                    $migration,
                                    $database->getConnectionString()
                                );
                            }
                        }

                        $migrationWasExecuted = new EventMigrationWasExecuted(
                            $database->getConnectionString(),
                            $file->getFilename(),
                            new DateTimeImmutable('now')
                        );

                        if ($commit === true) {
                            $this->logs->append($migrationWasExecuted);
                        }

                        $completedMigrations->completed($migrationWasExecuted);
                    } catch (QueryFailed $e) {
                        $completedMigrations->withError($e->getMessage());

                        return $completedMigrations;
                    } catch (PDOException $e) {
                        $completedMigrations->withError($e->getMessage());

                        return $completedMigrations;
                    }
                }
            }
        }

        return $completedMigrations;
    }
}
