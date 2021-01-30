<?php

namespace Turanct\Migraine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandSeed extends Command
{
    protected static $defaultName = 'seed';

    /**
     * @var MigrateUp
     */
    private $migrateUp;

    /**
     * @param MigrateUp $migrateUp
     *
     * @throws \LogicException
     */
    public function __construct(MigrateUp $migrateUp)
    {
        parent::__construct();

        $this->migrateUp = $migrateUp;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription('Apply seed')
            ->setHelp('Apply seed to a database defined in your configuration.');

        $this
            ->addOption(
                'seed',
                null,
                InputOption::VALUE_REQUIRED,
                'Which seed do you want to apply?',
                ''
            );

        $this
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_NONE,
                'Actually apply the seed instead of doing a dry-run.',
                null
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $seed = $input->getOption('seed');
        $seed = is_string($seed) ? $seed : '';

        $commit = (bool) $input->getOption('commit');

        try {
            $completedMigrations = $this->migrateUp->seed($commit, $seed);
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
