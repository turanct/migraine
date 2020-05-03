<?php

namespace Turanct\Migrations;

use DateTimeImmutable;

/**
 * @psalm-immutable
 */
final class EventMigrationWasExecuted implements Event
{
    private $host;
    private $database;
    private $migration;
    private $time;

    public function __construct(string $host, string $database, string $migration, DateTimeImmutable $time)
    {
        $this->host = $host;
        $this->database = $database;
        $this->migration = $migration;
        $this->time = $time;
    }

    /**
     * @return array{event: string, host: string, database: string, migration: string, time: int}
     */
    public function toArray(): array
    {
        return [
            'event' => static::class,
            'host' => $this->host,
            'database' => $this->database,
            'migration' => $this->migration,
            'time' => $this->time->getTimestamp(),
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return EventMigrationWasExecuted
     */
    public static function fromArray(array $array): Event
    {
        if (
            !isset($array['host'])
            || !isset($array['database'])
            || !isset($array['migration'])
            || !isset($array['time'])
        ) {
            throw new \InvalidArgumentException('Invalid array given');
        }

        try {
            $time = new DateTimeImmutable("@{$array['time']}");
        } catch (\Exception $e) {
            $time = new DateTimeImmutable('now');
        }

        return new static(
            (string) $array['host'],
            (string) $array['database'],
            (string) $array['migration'],
            $time
        );
    }
}
