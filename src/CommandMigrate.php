<?php

namespace Turanct\Migrations;

use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $migrateUp = new MigrateUp($this->translation, $this->logs);
        try {
            $completedMigrations = $migrateUp->migrateUp($this->workingDirectory);
        } catch (\Exception $e) {
            $output->writeln(get_class($e) . ": {$e->getMessage()}");

            return 1;
        }

        $completedMigrations = $completedMigrations->getList();
        foreach ($completedMigrations as $completedMigration) {
            $line = "✅ {$completedMigration->getConnectionString()} ⬅️  {$completedMigration->getMigration()}";
            $output->writeln($line);
        }

        return 0;
    }
}
