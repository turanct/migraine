<?php

namespace Turanct\Migrations;

use DateTimeImmutable;

final class NewMigration
{
    /**
     * @var ConfigTranslation
     */
    private $translation;

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(ConfigTranslation $translation, string $workingDirectory)
    {
        $this->translation = $translation;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @throws CouldNotGenerateConfig
     * @throws PleaseProvideValidGroupName
     */
    public function create(string $group, string $suffix): string
    {
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
            throw PleaseProvideValidGroupName::fromList($groupNames);
        }

        $now = new DateTimeImmutable('now');

        $name = $now->format('YmdHisv');
        $name = empty($suffix) ? $name : "{$name}-{$suffix}";
        $name .= '.sql';

        $migrationPath = "{$this->workingDirectory}/{$config->getMigrationsDirectory()}/{$group}/$name";

        touch($migrationPath);

        return $migrationPath;
    }
}
