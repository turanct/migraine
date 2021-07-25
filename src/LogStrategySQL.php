<?php

namespace Turanct\Migraine;

final class LogStrategySQL implements LogStrategy
{
    private $connectionString;
    private $user;
    private $password;
    private $table;

    public function __construct(string $connectionString, string $user, string $password, string $table)
    {
        $this->connectionString = $connectionString;
        $this->user = $user;
        $this->password = $password;
        $this->table = $table;
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
