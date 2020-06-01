<?php

namespace Turanct\Migrations;

use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription('Run migrations')
            ->setHelp('Run migrations defined in your configuration.');

        $this
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_NONE,
                'Actually run the migrations instead of doing a dry-run.',
                null
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commit = (bool) $input->getOption('commit');

        $migrateUp = new MigrateUp($this->translation, $this->logs);
        try {
            $completedMigrations = $migrateUp->migrateUp($this->workingDirectory, $commit);
        } catch (\Exception $e) {
            $output->writeln(get_class($e) . ": {$e->getMessage()}");

            return 1;
        }

        $listOfCompletedMigrations = $completedMigrations->getList();
        foreach ($listOfCompletedMigrations as $completedMigration) {
            $line = "✅ {$completedMigration->getConnectionString()} ⬅️  {$completedMigration->getMigration()}";
            $output->writeln($line);
        }

        if ($completedMigrations->failed()) {
            $output->writeln($completedMigrations->getError());
        }

        if ($commit !== true) {
            $line = 'The above is the result of a dry-run. If you want to execute this, add --commit to the command.';
            $output->writeln($line);
        }

        return 0;
    }
}
