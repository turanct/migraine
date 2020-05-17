<?php

namespace Turanct\Migrations;

/**
 * @psalm-suppress MissingConstructor
 */
final class QueryFailed extends \Exception
{
    /**
     * @var string
     */
    private $migration;

    /**
     * @var string
     */
    private $connection;

    public static function withMigrationData(string $message, string $migration = '', string $connection = ''): QueryFailed
    {
        $queryFailed = new static($message);
        $queryFailed->migration = $migration;
        $queryFailed->connection = $connection;

        return $queryFailed;
    }
}
