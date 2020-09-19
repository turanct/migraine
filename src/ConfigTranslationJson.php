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

        $migrationsDirectory = (string) $parsedJson['directory'] ?: 'migrations';

        $parsedGroups = (array) $parsedJson['groups'] ?: [];

        $groups = [];
        foreach ($parsedGroups as $name => $parsedGroup) {
            assert(is_string($name));
            assert(is_array($parsedGroup));

            $connection = (string) $parsedGroup['connection'] ?: '';
            $user = (string) $parsedGroup['user'] ?: '';
            $password = (string) $parsedGroup['password'] ?: '';

            $databases = [];

            if (!empty($connection)) {
                $databases[] = new Database($connection, $user, $password);
            }

            $fixedFields = array('connection', 'user', 'password');

            $shards = array_filter(
                array_keys($parsedGroup),
                function ($key) use ($fixedFields) {
                    return !in_array($key, $fixedFields, true);
                }
            );

            foreach ($shards as $shard) {
                /** @var array $shard */
                $shard = $parsedGroup[(string) $shard];

                $databaseConnection = (string) $shard['connection'] ?: $connection;
                $databaseUser = (string) $shard['user'] ?: $user;
                $databasePassword = (string) $shard['password'] ?: $password;

                $this->assertNotEmpty($databaseConnection);

                $databases[] = new Database(
                    $databaseConnection,
                    $databaseUser,
                    $databasePassword
                );
            }

            if (empty($databases)) {
                throw new CouldNotGenerateConfig();
            }

            $groups[] = new Group((string) $name, $databases);
        }

        return new Config($workingDirectory, $migrationsDirectory, $groups);
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
}
