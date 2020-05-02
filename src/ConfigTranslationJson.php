<?php

namespace Turanct\Migrations;

final class ConfigTranslationJson implements ConfigTranslation
{
    public function translate(string $json): Config
    {
        $parsedJson = (array) json_decode($json, true);
        if (empty($parsedJson)) {
            throw new CouldNotGenerateConfig();
        }

        $directory = (string) $parsedJson['directory'] ?: 'migrations';

        $parsedGroups = (array) $parsedJson['groups'] ?: [];

        $groups = [];
        foreach ($parsedGroups as $name => $parsedGroup) {
            assert(is_string($name));
            assert(is_array($parsedGroup));

            $host = (string) $parsedGroup['host'] ?: '';
            $user = (string) $parsedGroup['user'] ?: '';
            $password = (string) $parsedGroup['password'] ?: '';
            $database = (string) $parsedGroup['database'] ?: '';

            $databases = [];

            if (!empty($host) && !empty($user) && !empty($password) && !empty($database)) {
                $databases[] = new Database($host, $user, $password, $database);
            }

            $shards = array_filter(
                array_keys($parsedGroup),
                function ($key) {
                    $fixedFields = array('host', 'user', 'password', 'database');

                    return !in_array($key, $fixedFields, true);
                }
            );

            foreach ($shards as $shard) {
                /** @var array $shard */
                $shard = $parsedGroup[(string) $shard];

                $databaseHost = (string) $shard['host'] ?: $host;
                $databaseUser = (string) $shard['user'] ?: $user;
                $databasePassword = (string) $shard['password'] ?: $password;
                $databaseDatabase = (string) $shard['database'] ?: $database;

                $this->assertNotEmpty($databaseHost);
                $this->assertNotEmpty($databaseUser);
                $this->assertNotEmpty($databasePassword);
                $this->assertNotEmpty($databaseDatabase);

                $databases[] = new Database(
                    $databaseHost,
                    $databaseUser,
                    $databasePassword,
                    $databaseDatabase
                );
            }

            $groups[] = new Group((string) $name, $databases);
        }

        return new Config($directory, $groups);
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
