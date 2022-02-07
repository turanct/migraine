<?php

namespace Turanct\Migraine;

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
    private $seed;

    /**
     * @var string
     */
    private $connection;

    public static function withMigrationData(
        string $message,
        string $migration = '',
        string $connection = ''
    ): QueryFailed {
        $queryFailed = new static($message);
        $queryFailed->migration = $migration;
        $queryFailed->connection = $connection;

        return $queryFailed;
    }

    public static function withSeedData(
        string $message,
        string $seed = '',
        string $connection = ''
    ): QueryFailed {
        $queryFailed = new static($message);
        $queryFailed->seed = $seed;
        $queryFailed->connection = $connection;

        return $queryFailed;
    }
}
