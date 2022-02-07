<?php

namespace Turanct\Migraine;

final class CompletedSeeds
{
    /**
     * @var EventSeedWasFinished[]
     */
    private $events = [];

    /**
     * @var string
     */
    private $error = '';

    public function completed(EventSeedWasFinished $event): void
    {
        $this->events[] = $event;
    }

    public function withError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return EventSeedWasFinished[]
     */
    public function getList(): array
    {
        return $this->events;
    }

    public function failed(): bool
    {
        return !empty($this->error);
    }

    public function getError(): string
    {
        return $this->error;
    }
}
