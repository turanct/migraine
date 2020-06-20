<?php

namespace Turanct\Migrations;

use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandNew extends Command
{
    protected static $defaultName = 'new';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @throws \LogicException
     */
    public function __construct(Config $config)
    {
        parent::__construct();

        $this->config = $config;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $input->getArgument('group');
        $group = is_string($group) ? $group : '';

        $suffix = $input->getArgument('suffix');
        $suffix = is_string($suffix) ? $suffix : '';

        $newMigration = new NewMigration($this->config);

        try {
            $migrationPath = $newMigration->create($group, $suffix);
        } catch (PleaseProvideValidGroupName $e) {
            $output->writeln('Please provide a valid group name: ' . implode(', ', $e->getList()));

            return 1;
        }

        $output->writeln("Created new migration {$migrationPath}");

        return 0;
    }
}
