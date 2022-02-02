<?php

namespace Turanct\Migraine;

final class LogsDispatch implements Logs
{
    private $loggers;

    /**
     * @param Logs[] $loggers
     */
    public function __construct(array $loggers)
    {
        $this->loggers = $loggers;
    }

    public function acceptsStrategy(LogStrategy $strategy): bool
    {
        return true;
    }

    public function append(LogStrategy $strategy, Event $event): void
    {
        foreach ($this->loggers as $logger) {
            if ($logger->acceptsStrategy($strategy) === false) {
                continue;
            }

            $logger->append($strategy, $event);

            break;
        }
    }

    public function migrationWasExecuted(LogStrategy $strategy, string $connectionString, string $migration): bool
    {
        foreach ($this->loggers as $logger) {
            if ($logger->acceptsStrategy($strategy) === false) {
                continue;
            }

            return $logger->migrationWasExecuted($strategy, $connectionString, $migration);
        }

        return false;
    }

    public function seedWasExecuted(LogStrategy $strategy, string $connectionString, string $seed): bool
    {
        foreach ($this->loggers as $logger) {
            if ($logger->acceptsStrategy($strategy) === false) {
                continue;
            }

            return $logger->seedWasExecuted($strategy, $connectionString, $seed);
        }

        return false;
    }

    public function getAll(LogStrategy $strategy): array
    {
        foreach ($this->loggers as $logger) {
            if ($logger->acceptsStrategy($strategy) === false) {
                continue;
            }

            return $logger->getAll($strategy);
        }

        return [];
    }
}
