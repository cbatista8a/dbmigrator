<?php


namespace CubaDevOps\DbMigrator\domain;


use CubaDevOps\DbMigrator\domain\interfaces\DBConnection;

class Connection implements DBConnection
{
    protected string $host;
    protected string $port;
    protected string $username;
    protected string $password;
    protected string $database;
    protected string $driver;

    /**
     * Connection constructor.
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param $database
     * @param $driver
     */
    public function __construct(string $host,string $port,string $user,string $password,string $database,string $driver)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $user;
        $this->password = $password;
        $this->database = $database;
        $this->driver = $driver;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'database' => $this->database,
            'driver' => $this->driver,
        ];
    }
}