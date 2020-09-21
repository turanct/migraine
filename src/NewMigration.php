<?php

namespace Turanct\Migraine;

use DateTimeImmutable;

final class NewMigration
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(Config $config, Filesystem $filesystem, Clock $clock)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    /**
     * @param string $group
     * @param string $suffix
     *
     * @throws PleaseProvideValidGroupName
     *
     * @return string
     */
    public function create(string $group, string $suffix): string
    {
        $suffix = preg_replace('/[^a-zA-Z0-9]/i', '-', $suffix);

        $groups = $this->config->getGroups();

        $groupNames = array_map(
            function (Group $group): string {
                return $group->getName();
            },
            $groups
        );

        if (!in_array($group, $groupNames)) {
            throw PleaseProvideValidGroupName::fromList($groupNames);
        }

        $now = $this->clock->getTime();

        $name = $now->format('YmdHisv');
        $name = empty($suffix) ? $name : "{$name}-{$suffix}";
        $name .= '.sql';

        $migrationPath = "{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/{$group}/$name";

        if (!is_dir("{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}")) {
            $this->filesystem->mkdir(
                "{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}"
            );
        }

        if (!is_dir("{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/{$group}")) {
            $this->filesystem->mkdir(
                "{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/{$group}"
            );
        }

        $this->filesystem->touch($migrationPath);

        return $migrationPath;
    }
}
