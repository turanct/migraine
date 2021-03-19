<?php

namespace Turanct\Migraine;

use PDO;
use PDOException;

final class MigrateUp
{
    /**
     * @var GetConfig
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

    public function __construct(GetConfig $config, Logs $logs, Clock $clock)
    {
        $this->config = $config;
        $this->logs = $logs;
        $this->clock = $clock;
    }

    /**
     * @param bool $commit
     * @param string $onlyMigrateThisGroup
     *
     * @throws CouldNotGenerateConfig
     * @throws MigrationsDirectoryNotFound
     *
     * @return CompletedMigrations
     */
    public function migrateUp(bool $commit = false, string $onlyMigrateThisGroup = ''): CompletedMigrations
    {
        $config = $this->config->get();

        $completedMigrations = new CompletedMigrations();

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            if (!empty($onlyMigrateThisGroup) && $onlyMigrateThisGroup !== $group->getName()) {
                continue;
            }

            $finder = new \Symfony\Component\Finder\Finder();
            try {
                /** @psalm-suppress TooManyArguments */
                $files = $finder
                    ->files()
                    ->in("{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group->getName()}")
                    ->name('*.sql')
                    ->depth('==0')
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
                            $this->clock->getTime()
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

    /**
     * @param bool $commit
     * @param string $migrationName
     *
     * @throws CouldNotGenerateConfig
     *
     * @return CompletedMigrations
     */
    public function migrateSingle(bool $commit, string $migrationName): CompletedMigrations
    {
        $config = $this->config->get();

        $completedMigrations = new CompletedMigrations();

        if (empty($migrationName)) {
            $completedMigrations->withError('Provide a valid migration name.');

            return $completedMigrations;
        }

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $migrationPath = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/";
            $migrationPath.= "{$group->getName()}/{$migrationName}";

            $file = new \SplFileInfo($migrationPath);
            if (!$file->isFile()) {
                continue;
            }

            $migration = file_get_contents($file->getRealPath());

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
                        $this->clock->getTime()
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

        return $completedMigrations;
    }

    /**
     * @param bool $commit
     * @param string $seedName
     *
     * @throws CouldNotGenerateConfig
     *
     * @return CompletedMigrations
     */
    public function seed(bool $commit, string $seedName): CompletedMigrations
    {
        $config = $this->config->get();

        $completedMigrations = new CompletedMigrations();

        if (empty($seedName)) {
            $completedMigrations->withError('Provide a valid seed name.');

            return $completedMigrations;
        }

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $migrationPath = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/";
            $migrationPath.= "{$group->getName()}/{$seedName}";

            $file = new \SplFileInfo($migrationPath);
            if (!$file->isFile()) {
                continue;
            }

            $migration = file_get_contents($file->getRealPath());

            $databases = $group->getDatabases();

            foreach ($databases as $database) {
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
                        $this->clock->getTime()
                    );

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

        return $completedMigrations;
    }
}
