<?php

namespace Turanct\Migraine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandNewSeed extends Command
{
    protected static $defaultName = 'new-seed';

    /**
     * @var NewMigration
     */
    private $newSeed;

    /**
     * @param NewSeed $newSeed
     *
     * @throws \LogicException
     */
    public function __construct(NewSeed $newSeed)
    {
        parent::__construct();

        $this->newSeed = $newSeed;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription('Create a new seed file')
            ->setHelp('Create a new seed file in your seeds directory');

        $this
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Which group do you want to create a seed for?'
            )
        ;

        $this
            ->addArgument(
                'suffix',
                InputArgument::OPTIONAL,
                'What suffix do you want your seed to have?',
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
            $migrationPath = $this->newSeed->create($group, $suffix);
        } catch (PleaseProvideValidGroupName $e) {
            $output->writeln('Please provide a valid group name: ' . implode(', ', $e->getList()));

            return 1;
        }

        $output->writeln("Created new migration {$migrationPath}");
        $output->writeln("Don't forget to update relevant seeds if needed.");

        return 0;
    }
}
