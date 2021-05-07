<?php

namespace Turanct\Migraine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandSkip extends Command
{
    protected static $defaultName = 'skip';

    /**
     * @var Skip
     */
    private $skip;

    /**
     * @param Skip $skip
     *
     * @throws \LogicException
     */
    public function __construct(Skip $skip)
    {
        parent::__construct();

        $this->skip = $skip;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription('Skip a migration')
            ->setHelp('Skip a migration that you know already ran.');

        $this
            ->addOption(
                'migration',
                null,
                InputOption::VALUE_REQUIRED,
                'Define the migration',
                ''
            );

        $this
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_NONE,
                'Actually skip instead of doing a dry-run.',
                null
            );
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGenerateConfig
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        $commit = (bool) $input->getOption('commit');

        $migration = $input->getOption('migration');
        $migration = is_string($migration) ? $migration : '';

        if (!empty($migration)) {
            $completedMigrations = $this->skip->skipSingle($commit, $migration);
        } else {
            $output->writeln("Please provide a migration to skip.");

            return 1;
        }

        $listOfCompletedMigrations = $completedMigrations->getList();
        foreach ($listOfCompletedMigrations as $completedMigration) {
            $line = "⏭  {$completedMigration->getConnectionString()} ⬅️  {$completedMigration->getMigration()}";
            $output->writeln($line);
        }

        if ($completedMigrations->failed()) {
            $output->writeln($completedMigrations->getError());

            $exitCode = 1;
        }

        if ($commit !== true) {
            $line = 'The above is the result of a dry-run. If you want to execute this, add --commit to the command.';
            $output->writeln($line);
        }

        return $exitCode;
    }
}
