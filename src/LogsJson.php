<?php

namespace Turanct\Migraine;

final class LogsJson implements Logs
{
    public function acceptsStrategy(LogStrategy $strategy): bool
    {
        return $strategy instanceof LogStrategyJson;
    }

    public function append(LogStrategy $strategy, Event $event): void
    {
        if (!$strategy instanceof LogStrategyJson) {
            return;
        }

        $events = $this->readFromFile($strategy->getFile());

        $events[] = $event;

        $this->writeToFile($strategy->getFile(), $events);
    }

    public function migrationWasExecuted(LogStrategy $strategy, string $connectionString, string $migration): bool
    {
        if (!$strategy instanceof LogStrategyJson) {
            return false;
        }

        $events = $this->readFromFile($strategy->getFile());

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

    public function getAll(LogStrategy $strategy): array
    {
        if (!$strategy instanceof LogStrategyJson) {
            return [];
        }

        return $this->readFromFile($strategy->getFile());
    }

    /**
     * @param string $file
     *
     * @return Event[]
     */
    private function readFromFile(string $file): array
    {
        $contents = '[]';

        if (is_file($file)) {
            $contents = file_get_contents($file);

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
     * @param string $file
     * @param Event[] $events
     */
    private function writeToFile(string $file, array $events): void
    {
        $events = array_map(
            function (Event $event) {
                return $event->toArray();
            },
            $events
        );

        file_put_contents($file, json_encode($events, JSON_PRETTY_PRINT));
    }
}
