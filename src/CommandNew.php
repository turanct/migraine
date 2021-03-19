<?php

namespace Turanct\Migraine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandNew extends Command
{
    protected static $defaultName = 'new';

    /**
     * @var NewMigration
     */
    private $newMigration;

    /**
     * @param NewMigration $newMigration
     *
     * @throws \LogicException
     */
    public function __construct(NewMigration $newMigration)
    {
        parent::__construct();

        $this->newMigration = $newMigration;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription('Create a new migration file')
            ->setHelp('Create a new migration file in your migrations directory');

        $this
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Which group do you want to create a migration for?'
            )
        ;

        $this
            ->addArgument(
                'suffix',
                InputArgument::OPTIONAL,
                'What suffix do you want your migration to have?',
                'migration'
            )
        ;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGenerateConfig
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $input->getArgument('group');
        $group = is_string($group) ? $group : '';

        $suffix = $input->getArgument('suffix');
        $suffix = is_string($suffix) ? $suffix : '';

        try {
            $migrationPath = $this->newMigration->create($group, $suffix);
        } catch (PleaseProvideValidGroupName $e) {
            $output->writeln('Please provide a valid group name: ' . implode(', ', $e->getList()));

            return 1;
        }

        $output->writeln("Created new migration {$migrationPath}");

        return 0;
    }
}
