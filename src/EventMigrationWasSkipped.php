<?php

namespace Turanct\Migraine;

use DateTimeImmutable;

/**
 * @psalm-immutable
 */
final class EventMigrationWasSkipped implements EventMigrationWasFinished
{
    private $connectionString;
    private $migration;
    private $time;

    public function __construct(string $connectionString, string $migration, DateTimeImmutable $time)
    {
        $this->connectionString = $connectionString;
        $this->migration = $migration;
        $this->time = $time;
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

    public function getMigration(): string
    {
        return $this->migration;
    }

    /**
     * @return array{event: string, connectionString: string, migration: string, time: int}
     */
    public function toArray(): array
    {
        return [
            'event' => static::class,
            'connectionString' => $this->connectionString,
            'migration' => $this->migration,
            'time' => $this->time->getTimestamp(),
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return EventMigrationWasSkipped
     */
    public static function fromArray(array $array): Event
    {
        if (
            !isset($array['connectionString'])
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
            (string) $array['connectionString'],
            (string) $array['migration'],
            $time
        );
    }
}
