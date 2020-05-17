<?php

namespace Turanct\Migrations;

/**
 * @psalm-immutable
 */
final class Database
{
    private $connectionString;
    private $user;
    private $password;

    public function __construct(string $connectionString, string $user, string $password)
    {
        $this->connectionString = $connectionString;
        $this->user = $user;
        $this->password = $password;
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
}
