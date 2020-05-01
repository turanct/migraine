<?php

namespace Turanct\Migrations;

final class Database
{
    private $host;
    private $user;
    private $password;
    private $database;

    public function __construct(string $host, string $user, string $password, string $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
    }
}
