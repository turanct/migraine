<?php

namespace Turanct\Migraine;

/**
 * @psalm-immutable
 */
final class LogStrategyJson implements LogStrategy
{
    /**
     * @var string
     */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}
