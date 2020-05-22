<?php

namespace Turanct\Migrations;

use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandMigrate extends Command
{
    protected static $defaultName = 'migrate';

    /**
     * @var ConfigTranslation
     */
    private $translation;

    /**
     * @var Logs
     */
    private $logs;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @throws \LogicException
     */
    public function __construct(ConfigTranslation $translation, Logs $logs, string $workingDirectory)
    {
        parent::__construct();

        $this->translation = $translation;
        $this->logs = $logs;
        $this->workingDirectory = $workingDirectory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Run migrations')
            ->setHelp('Run migrations defined in your configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = "{$this->workingDirectory}/migrations.json";

        if (!is_file($configFile)) {
            $output->writeln('Config file could not be read.');

            return 1;
        }

        $filecontents = file_get_contents($configFile);

        try {
            $config = $this->translation->translate($filecontents);
        } catch (CouldNotGenerateConfig $e) {
            $output->writeln('Config file could not be read.');

            return 1;
        }

        $completedMigrations = new CompletedMigrations();

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $finder = new \Symfony\Component\Finder\Finder();
            try {
                /** @psalm-suppress TooManyArguments */
                $files = $finder
                    ->files()
                    ->in("{$this->workingDirectory}/{$config->getMigrationsDirectory()}/{$group->getName()}")
                    ->name('*.sql')
                    ->sortByName(true);
            } catch (\Symfony\Component\Finder\Exception\DirectoryNotFoundException $e) {
                $output->writeln('Migrations directory could not be found.');

                return 1;
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
                        $db = new PDO(
                            $database->getConnectionString(),
                            $database->getUser(),
                            $database->getPassword()
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

                        $migrationWasExecuted = new EventMigrationWasExecuted(
                            $database->getConnectionString(),
                            $file->getFilename(),
                            new \DateTimeImmutable('now')
                        );

                        $this->logs->append($migrationWasExecuted);
                        $completedMigrations->completed($migrationWasExecuted);
                    } catch (QueryFailed $e) {
                        $output->writeln($e->getMessage());
                    } catch (PDOException $e) {
                        $output->writeln('Failed');
                    }
                }
            }
        }

        $completedMigrations = $completedMigrations->getList();
        foreach ($completedMigrations as $completedMigration) {
            $output->writeln("✅ {$completedMigration->getConnectionString()} ⬅️ {$completedMigration->getMigration()}");
        }

        return 0;
    }
}
