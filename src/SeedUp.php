<?php

namespace Turanct\Migraine;

use PDO;
use PDOException;

final class SeedUp
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
     *
     * @return CompletedSeeds
     */
    public function seedUp(bool $commit = false, string $onlyMigrateThisGroup = ''): CompletedSeeds
    {
        $config = $this->config->get();
        $logStrategy = $config->getLogStrategy();

        $completedSeeds = new CompletedSeeds();

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
                    ->in("{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group->getName()}/seeds")
                    ->name('*.sql')
                    ->depth('==0')
                    ->sortByName(true);
            } catch (\Symfony\Component\Finder\Exception\DirectoryNotFoundException $e) {
                // Seeds dir is not mandatory, so if the directory doesn't exist ignore the exception.
            }

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($files as $file) {
                $seed = $file->getContents();

                $databases = $group->getDatabases();

                foreach ($databases as $database) {
                    if ($this->logs->seedWasExecuted($logStrategy, $database->getConnectionString(), $file->getFilename())) {
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

                            $result = $db->exec($seed);

                            if ($result === false) {
                                $errorInfo = $db->errorInfo();

                                throw QueryFailed::withMigrationData(
                                    (string) $errorInfo[2],
                                    $seed,
                                    $database->getConnectionString()
                                );
                            }
                        }

                        $seedWasExecuted = new EventSeedWasExecuted(
                            $database->getConnectionString(),
                            $file->getFilename(),
                            $this->clock->getTime()
                        );

                        if ($commit === true) {
                            $this->logs->append($logStrategy, $seedWasExecuted);
                        }

                        $completedSeeds->completed($seedWasExecuted);
                    } catch (QueryFailed $e) {
                        $completedSeeds->withError($e->getMessage());

                        return $completedSeeds;
                    } catch (PDOException $e) {
                        $completedSeeds->withError($e->getMessage());

                        return $completedSeeds;
                    }
                }
            }
        }

        return $completedSeeds;
    }

    /**
     * @param bool $commit
     * @param string $seedName
     *
     * @throws CouldNotGenerateConfig
     *
     * @return CompletedSeeds
     */
    public function seed(bool $commit, string $seedName): CompletedSeeds
    {
        $config = $this->config->get();

        $completedSeeds = new CompletedSeeds();

        if (empty($seedName)) {
            $completedSeeds->withError('Provide a valid seed name.');

            return $completedSeeds;
        }

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $seedPath = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/";
            $seedPath.= "{$group->getName()}/{$seedName}";

            $file = new \SplFileInfo($seedPath);
            if (!$file->isFile()) {
                continue;
            }

            $seed = file_get_contents($file->getRealPath());

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

                        $result = $db->exec($seed);

                        if ($result === false) {
                            $errorInfo = $db->errorInfo();

                            throw QueryFailed::withSeedData(
                                (string) $errorInfo[2],
                                $seed,
                                $database->getConnectionString()
                            );
                        }
                    }

                    $seedWasExecuted = new EventSeedWasExecuted(
                        $database->getConnectionString(),
                        $file->getFilename(),
                        $this->clock->getTime()
                    );

                    $completedSeeds->completed($seedWasExecuted);
                } catch (QueryFailed $e) {
                    $completedSeeds->withError($e->getMessage());

                    return $completedSeeds;
                } catch (PDOException $e) {
                    $completedSeeds->withError($e->getMessage());

                    return $completedSeeds;
                }
            }
        }

        return $completedSeeds;
    }
}
