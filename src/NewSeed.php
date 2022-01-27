<?php

namespace Turanct\Migraine;

final class NewSeed
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

        $seedPath = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}";

        if (!is_dir($seedPath)) {
            $this->filesystem->mkdir($seedPath);
        }

        $seedPath .= "/{$group}";

        if (!is_dir($seedPath)) {
            $this->filesystem->mkdir($seedPath);
        }

        $seedPath .= "/seeds";


        if (!is_dir($seedPath)) {
            $this->filesystem->mkdir($seedPath);
        }

        $seedPath .= "/{$name}";

        $this->filesystem->touch($seedPath);

        return $seedPath;
    }
}
