<?php

namespace Turanct\Migraine;

final class LogsJson implements Logs
{
    /**
     * @var string
     */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function append(Event $event): void
    {
        $events = $this->readFromFile();

        $events[] = $event;

        $this->writeToFile($events);
    }

    public function migrationWasExecuted(string $connectionString, string $migration): bool
    {
        $events = $this->readFromFile();

        foreach ($events as $event) {
            $event = $event->toArray();

            if (
                in_array($event['event'], [EventMigrationWasExecuted::class, EventMigrationWasSkipped::class])
                && $event['connectionString'] === $connectionString
                && $event['migration'] === $migration
            ) {
                return true;
            }
        }

        return false;
    }

    public function getAll(): array
    {
        return $this->readFromFile();
    }

    /**
     * @return Event[]
     */
    private function readFromFile(): array
    {
        $contents = '[]';

        if (is_file($this->file)) {
            $contents = file_get_contents($this->file);

            if ($contents === false) {
                $contents = '[]';
            }
        }

        $events = (array) json_decode($contents, true);

        $events = array_map(
            function (array $event) {
                assert(is_string($event['event']));

                try {
                    if ($event['event'] === EventMigrationWasExecuted::class) {
                        return EventMigrationWasExecuted::fromArray($event);
                    }
                    if ($event['event'] === EventMigrationWasSkipped::class) {
                        return EventMigrationWasSkipped::fromArray($event);
                    }
                } catch (\InvalidArgumentException $e) {
                    return null;
                }

                return null;
            },
            $events
        );

        $events = array_filter($events);

        return $events;
    }

    /**
     * @param Event[] $events
     */
    private function writeToFile($events): void
    {
        $events = array_map(
            function (Event $event) {
                return $event->toArray();
            },
            $events
        );

        file_put_contents($this->file, json_encode($events, JSON_PRETTY_PRINT));
    }
}
