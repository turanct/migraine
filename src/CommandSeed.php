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
     * @var SeedUp
     */
    private $seedUp;

    /**
     * @param SeedUp $seedUp
     *
     * @throws \LogicException
     */
    public function __construct(SeedUp $seedUp)
    {
        parent::__construct();

        $this->seedUp = $seedUp;
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
     * @throws CouldNotGenerateConfig
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        $seed = $input->getOption('seed');
        $seed = is_string($seed) ? $seed : '';

        $commit = (bool) $input->getOption('commit');

        $completedSeeds = $this->seedUp->seed($commit, $seed);

        $listOfCompletedSeeds = $completedSeeds->getList();
        foreach ($listOfCompletedSeeds as $completedSeed) {
            $line = "✅ {$completedSeed->getConnectionString()} ⬅️  {$completedSeed->getSeed()}";
            $output->writeln($line);
        }

        if ($completedSeeds->failed()) {
            $output->writeln($completedSeeds->getError());

            $exitCode = 1;
        }

        if ($commit !== true) {
            $line = 'The above is the result of a dry-run. If you want to execute this, add --commit to the command.';
            $output->writeln($line);
        }

        return $exitCode;
    }
}
