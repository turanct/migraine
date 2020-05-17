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

        $groups = $config->getGroups();

        foreach ($groups as $group) {
            $output->writeln("Migrating group \"{$group->getName()}\"");
            $output->writeln('-------------------------------------------------------');

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

                $output->writeln('Running migration:');
                $output->writeln('-------------------------------------------------------');
                $output->writeln($migration);
                $output->writeln('-------------------------------------------------------');
                $output->writeln('On these databases:');
                $output->writeln('-------------------------------------------------------');

                foreach ($databases as $database) {
                    if ($this->logs->migrationWasExecuted($database->getConnectionString(), $file->getFilename())) {
                        continue;
                    }

                    $dbString = "- {$database->getConnectionString()}";

                    $output->writeln($dbString);

                    try {
                        $db = new PDO(
                            $database->getConnectionString(),
                            $database->getUser(),
                            $database->getPassword()
                        );

                        $db->query($migration);
                    } catch (PDOException $e) {
                        $output->writeln('Failed');
                    }

                    $this->logs->append(
                        new EventMigrationWasExecuted(
                            $database->getConnectionString(),
                            $file->getFilename(),
                            new \DateTimeImmutable('now')
                        )
                    );
                }

                $output->writeln('-------------------------------------------------------');
            }
        }

        return 0;
    }
}
