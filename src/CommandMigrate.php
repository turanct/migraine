<?php

namespace Turanct\Migraine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandMigrate extends Command
{
    protected static $defaultName = 'migrate';

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
            ->setDescription('Run migrations')
            ->setHelp('Run migrations defined in your configuration.');

        $this
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Migrate a single group',
                ''
            );

        $this
            ->addOption(
                'migration',
                null,
                InputOption::VALUE_OPTIONAL,
                'Migrate a single migration',
                ''
            );

        $this
            ->addOption(
                'seed',
                null,
                InputOption::VALUE_NONE,
                'In addition to the migration, run all the seeds.',
                null
            );

        $this
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_NONE,
                'Actually run the migrations instead of doing a dry-run.',
                null
            );

        $this
            ->addOption(
                'silent',
                null,
                InputOption::VALUE_NONE,
                'Do not output anything if there are no migrations being run.',
                null
            );
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGenerateConfig
     * @throws MigrationsDirectoryNotFound
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        $commit = (bool) $input->getOption('commit');
        $silent = (bool) $input->getOption('silent');
        $seed = (bool) $input->getOption('seed');

        $group = $input->getOption('group');
        $group = is_string($group) ? $group : '';

        $singleMigration = $input->getOption('migration');
        $singleMigration = is_string($singleMigration) ? $singleMigration : '';

        if (!empty($singleMigration)) {
            $completedMigrations = $this->migrateUp->migrateSingle($commit, $singleMigration);
        } else {
            $completedMigrations = $this->migrateUp->migrateUp($commit, $group, $seed);
        }

        $listOfCompletedMigrations = $completedMigrations->getList();
        foreach ($listOfCompletedMigrations as $completedMigration) {
            $line = "✅ {$completedMigration->getConnectionString()} ⬅️  {$completedMigration->getMigration()}";
            $output->writeln($line);
        }

        if ($completedMigrations->failed()) {
            $output->writeln($completedMigrations->getError());

            $exitCode = 1;
        }

        if ($commit !== true && $silent !== true) {
            $line = 'The above is the result of a dry-run. If you want to execute this, add --commit to the command.';
            $output->writeln($line);
        }

        return $exitCode;
    }
}
