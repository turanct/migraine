<?php

namespace Turanct\Migrations;

use DateTimeImmutable;

final class NewMigration
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws PleaseProvideValidGroupName
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

        $now = new DateTimeImmutable('now');

        $name = $now->format('YmdHisv');
        $name = empty($suffix) ? $name : "{$name}-{$suffix}";
        $name .= '.sql';

        $migrationPath = "{$this->config->getWorkingDirectory()}/{$this->config->getMigrationsDirectory()}/{$group}/$name";

        touch($migrationPath);

        return $migrationPath;
    }
}
