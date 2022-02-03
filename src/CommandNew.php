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
     * @var string
     */
    private static $migrationAction = 'migration';

    /**
     * @var string
     */
    private static $seedAction = 'seed';

    /**
     * @var NewMigration
     */
    private $newMigration;

    /**
     * @var NewSeed
     */
    private $newSeed;

    /**
     * @param NewMigration $newMigration
     * @param NewSeed $newSeed
     */
    public function __construct(NewMigration $newMigration, NewSeed $newSeed)
    {
        parent::__construct();

        $this->newMigration = $newMigration;
        $this->newSeed = $newSeed;
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
                'action',
                InputArgument::REQUIRED,
                'What type of file do you wish to create? (migration or seed?)'
            )
        ;

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
        $action = $input->getArgument('action');
        $action = is_string($action) ? $action : self::$migrationAction;

        $group = $input->getArgument('group');
        $group = is_string($group) ? $group : '';

        $suffix = $input->getArgument('suffix');
        $suffix = is_string($suffix) ? $suffix : '';

        try {
            switch ($action) {
                case self::$seedAction:
                    $migrationPath = $this->newSeed->create($group, $suffix);
                    break;
                default:
                    $migrationPath = $this->newMigration->create($group, $suffix);
                    break;
            }
        } catch (PleaseProvideValidGroupName $e) {
            $output->writeln('Please provide a valid group name: ' . implode(', ', $e->getList()));

            return 1;
        }

        $output->writeln("Created new {$action} {$migrationPath}");
        $output->writeln("Don't forget to update relevant migrations or seeds if needed.");

        return 0;
    }
}
