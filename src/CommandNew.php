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
     * @var ConfigTranslation
     */
    private $translation;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @throws \LogicException
     */
    public function __construct(ConfigTranslation $translation, string $workingDirectory)
    {
        parent::__construct();

        $this->translation = $translation;
        $this->workingDirectory = $workingDirectory;
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
     * @throws CouldNotGenerateConfig
     * @throws MigrationsDirectoryNotFound
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $input->getArgument('group');
        $group = is_string($group) ? $group : '';

        $suffix = $input->getArgument('suffix');
        $suffix = is_string($suffix) ? $suffix : '';
        $suffix = preg_replace('/[^a-zA-Z0-9]/i', '-', $suffix);

        $configFile = "{$this->workingDirectory}/migrations.json";

        if (!is_file($configFile)) {
            throw new CouldNotGenerateConfig();
        }

        $filecontents = file_get_contents($configFile);

        $config = $this->translation->translate($filecontents);

        $groups = $config->getGroups();

        $groupNames = array_map(
            function (Group $group): string {
                return $group->getName();
            },
            $groups
        );

        if (!in_array($group, $groupNames)) {
            $output->writeln('Please provide a valid group name: ' . implode(', ', $groupNames));

            return 1;
        }

        $now = new DateTimeImmutable('now');

        $name = $now->format('YmdHisv');
        $name = empty($suffix) ? $name : "{$name}-{$suffix}";
        $name .= '.sql';

        $migrationPath = "{$this->workingDirectory}/{$config->getMigrationsDirectory()}/{$group}/$name";

        touch($migrationPath);

        $output->writeln("Created new migration {$migrationPath}");

        return 0;
    }
}
