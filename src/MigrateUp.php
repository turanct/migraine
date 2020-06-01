<?php

namespace Turanct\Migrations;

use DateTimeImmutable;
use PDO;
use PDOException;

final class MigrateUp
{
    /**
     * @var ConfigTranslation
     */
    private $translation;

    /**
     * @var Logs
     */
    private $logs;

    public function __construct(ConfigTranslation $translation, Logs $logs)
    {
        $this->translation = $translation;
        $this->logs = $logs;
    }

    /**
     * @throws CouldNotGenerateConfig
     * @throws MigrationsDirectoryNotFound
     */
    public function migrateUp(string $workingDirectory, bool $commit = false): CompletedMigrations
    {
        $configFile = "{$workingDirectory}/migrations.json";

        if (!is_file($configFile)) {
            throw new CouldNotGenerateConfig();
        }

        $filecontents = file_get_contents($configFile);

        $config = $this->translation->translate($filecontents);

        $completedMigrations = new CompletedMigrations();

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $finder = new \Symfony\Component\Finder\Finder();
            try {
                /** @psalm-suppress TooManyArguments */
                $files = $finder
                    ->files()
                    ->in("{$workingDirectory}/{$config->getMigrationsDirectory()}/{$group->getName()}")
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

                            $result = $db->query($migration);

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
                        return $completedMigrations;
                    } catch (PDOException $e) {
                        return $completedMigrations;
                    }
                }
            }
        }

        return $completedMigrations;
    }
}
