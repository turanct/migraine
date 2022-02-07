<?php

namespace Turanct\Migraine;

use PDO;

final class LogsSQL implements Logs
{
    public function acceptsStrategy(LogStrategy $strategy): bool
    {
        return $strategy instanceof LogStrategySQL;
    }

    public function append(LogStrategy $strategy, Event $event): void
    {
        if (!$strategy instanceof LogStrategySQL) {
            return;
        }

        $db = new PDO(
            $strategy->getConnectionString(),
            $strategy->getUser(),
            $strategy->getPassword(),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $statement = $db->prepare("INSERT INTO `{$strategy->getTable()}` (`event`) VALUES (:event)");
        $statement->execute([':event' => json_encode($event->toArray())]);
    }

    public function migrationWasExecuted(LogStrategy $strategy, string $connectionString, string $migration): bool
    {
        if (!$strategy instanceof LogStrategySQL) {
            return false;
        }

        $db = new PDO(
            $strategy->getConnectionString(),
            $strategy->getUser(),
            $strategy->getPassword(),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $statement = $db->prepare("SELECT * FROM `{$strategy->getTable()}`");
        $statement->execute();

        $events = $statement->fetchAll();

        $events = $this->mapRowsToEvents($events);

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

    public function seedWasExecuted(LogStrategy $strategy, string $connectionString, string $seed): bool
    {
        if (!$strategy instanceof LogStrategySQL) {
            return false;
        }

        $db = new PDO(
            $strategy->getConnectionString(),
            $strategy->getUser(),
            $strategy->getPassword(),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $query = "SELECT `event` FROM `{$strategy->getTable()}`";
        $statement = $db->prepare($query);
        $statement->execute();

        $events = $statement->fetchAll();

        $events = $this->mapRowsToEvents($events);

        foreach ($events as $event) {
            $event = $event->toArray();

            if (
                $event['event'] == EventSeedWasExecuted::class
                && $event['connectionString'] === $connectionString
                && $event['seed'] === $seed
            ) {
                return true;
            }
        }

        return false;
    }

    public function getAll(LogStrategy $strategy): array
    {
        if (!$strategy instanceof LogStrategySQL) {
            return [];
        }

        $db = new PDO(
            $strategy->getConnectionString(),
            $strategy->getUser(),
            $strategy->getPassword(),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $statement = $db->prepare("SELECT * FROM `{$strategy->getTable()}`");
        $statement->execute();

        $events = $statement->fetchAll();

        $events = $this->mapRowsToEvents($events);

        return $events;
    }

    /**
     * @param array $rows
     *
     * @return Event[]
     */
    protected function mapRowsToEvents(array $rows): array
    {
        $events = array_map(
            function (array $row) {
                assert(is_string($row['event']));

                $event = json_decode($row['event'], true);
                assert(is_array($event));

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
            $rows
        );

        $events = array_filter($events);

        return $events;
    }
}
