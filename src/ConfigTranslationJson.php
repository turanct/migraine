<?php

namespace Turanct\Migraine;

final class ConfigTranslationJson implements ConfigTranslation
{
    public function translate(string $workingDirectory, string $json): Config
    {
        $parsedJson = (array) json_decode($json, true);
        if (empty($parsedJson)) {
            throw new CouldNotGenerateConfig();
        }

        $fixedFields = array('connection', 'user', 'password');

        $migrationsDirectory = (string) $parsedJson['directory'] ?: 'migrations';

        $logStrategy = $this->determineLogStrategy($workingDirectory, $parsedJson);

        $parsedGroups = (array) $parsedJson['groups'] ?: [];

        $user = (string) ($parsedGroups['user'] ?? '');
        $password = (string) ($parsedGroups['password'] ?? '');

        $groups = [];

        foreach ($fixedFields as $fixedField) {
            unset($parsedGroups[$fixedField]);
        }

        foreach ($parsedGroups as $name => $parsedGroup) {
            assert(is_string($name));
            assert(is_array($parsedGroup));

            $groupConnection = (string) ($parsedGroup['connection'] ?? '');
            $groupUser = (string) ($parsedGroup['user'] ?? $user);
            $groupPassword = (string) ($parsedGroup['password'] ?? $password);

            $databases = [];

            if (!empty($groupConnection)) {
                $databases[] = new Database($groupConnection, $groupUser, $groupPassword);
            }

            $shards = array_filter(
                array_keys($parsedGroup),
                function ($key) use ($fixedFields) {
                    return !in_array($key, $fixedFields, true);
                }
            );

            foreach ($shards as $shard) {
                /** @var array $shard */
                $shard = $parsedGroup[(string) $shard];

                $databaseConnection = (string) ($shard['connection'] ?? $groupConnection);
                $databaseUser = (string) ($shard['user'] ?? $groupUser);
                $databasePassword = (string) ($shard['password'] ?? $groupPassword);

                $this->assertNotEmpty($databaseConnection);

                $databases[] = new Database($databaseConnection, $databaseUser, $databasePassword);
            }

            if (empty($databases)) {
                throw new CouldNotGenerateConfig();
            }

            $groups[] = new Group((string) $name, $databases);
        }

        return new Config($workingDirectory, $migrationsDirectory, $logStrategy, $groups);
    }

    /**
     * @throws CouldNotGenerateConfig
     */
    private function assertNotEmpty(string $value)
    {
        if (empty($value)) {
            throw new CouldNotGenerateConfig();
        }
    }

    protected function determineLogStrategy(string $workingDirectory, array $parsedJson): LogStrategy
    {
        $logs = new LogStrategyJson("{$workingDirectory}/logs.json");

        if (!isset($parsedJson['logs']) || !isset($parsedJson['logs']['type'])) {
            return $logs;
        }

        if (
            $parsedJson['logs']['type'] === 'file'
            && isset($parsedJson['logs']['file'])
            && is_string($parsedJson['logs']['file'])
        ) {
            return new LogStrategyJson("{$workingDirectory}/{$parsedJson['logs']['file']}");
        }

        if ($parsedJson['logs']['type'] === 'sql') {
            $connection = (string) ($parsedJson['logs']['connection'] ?? '');
            $table = (string) ($parsedJson['logs']['table'] ?? '');
            $user = (string) ($parsedJson['logs']['user'] ?? '');
            $password = (string) ($parsedJson['logs']['password'] ?? '');

            return new LogStrategySQL($connection, $user, $password, $table);
        }

        return $logs;
    }
}
