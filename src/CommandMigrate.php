<?php

namespace Turanct\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandMigrate extends Command
{
    protected static $defaultName = 'migrate';

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

    protected function configure():void
    {
        $this
            ->setDescription('Run migrations')
            ->setHelp('Run migrations defined in your configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = "{$this->workingDirectory}/migrations.json";

        $filecontents = file_get_contents($configFile);

        try {
            $config = $this->translation->translate($filecontents);
        } catch (CouldNotGenerateConfig $e) {
        }

        return 0;
    }
}
