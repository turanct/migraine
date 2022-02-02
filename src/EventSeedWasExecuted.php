<?php

namespace Turanct\Migraine;

use DateTimeImmutable;

/**
 * @psalm-immutable
 */
final class EventSeedWasExecuted implements EventSeedWasFinished
{
    private $connectionString;
    private $seed;
    private $time;

    public function __construct(string $connectionString, string $seed, DateTimeImmutable $time)
    {
        $this->connectionString = $connectionString;
        $this->seed = $seed;
        $this->time = $time;
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

    public function getSeed(): string
    {
        return $this->seed;
    }

    /**
     * @return array{event: string, connectionString: string, seed: string, time: int}
     */
    public function toArray(): array
    {
        return [
            'event' => static::class,
            'connectionString' => $this->connectionString,
            'seed' => $this->seed,
            'time' => $this->time->getTimestamp(),
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return EventSeedWasExecuted
     */
    public static function fromArray(array $array): Event
    {
        if (
            !isset($array['connectionString'])
            || !isset($array['seed'])
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
            (string) $array['seed'],
            $time
        );
    }
}
