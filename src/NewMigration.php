<?php

namespace Turanct\Migraine;

final class NewMigration
{
    /**
     * @var GetConfig
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

    public function __construct(GetConfig $config, Filesystem $filesystem, Clock $clock)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    /**
     * @param string $group
     * @param string $suffix
     *
     * @throws CouldNotGenerateConfig
     * @throws PleaseProvideValidGroupName
     *
     * @return string
     */
    public function create(string $group, string $suffix): string
    {
        $config = $this->config->get();

        $suffix = preg_replace('/[^a-zA-Z0-9]/i', '-', $suffix);

        $groups = $config->getGroups();

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

        $migrationPath = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group}/$name";

        if (!is_dir("{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}")) {
            $this->filesystem->mkdir(
                "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}"
            );
        }

        if (!is_dir("{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group}")) {
            $this->filesystem->mkdir(
                "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group}"
            );
        }

        $this->filesystem->touch($migrationPath);

        return $migrationPath;
    }
}
