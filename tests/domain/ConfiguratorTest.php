<?php

namespace CubaDevOps\DbMigrator\Tests\domain;

use CubaDevOps\DbMigrator\domain\Configurator;
use CubaDevOps\DbMigrator\domain\Connection;
use CubaDevOps\DbMigrator\domain\exceptions\FileExistsException;
use PHPUnit\Framework\TestCase;

class ConfiguratorTest extends TestCase
{
    private Connection $connection;

    public function setUp(): void
    {
        $this->connection = new Connection('localhost', '3360', 'root', 'root', 'test', 'mysqli');
    }

    public function testSetPath()
    {
        $this->expectException(FileExistsException::class);
        $configurator = new Configurator($this->connection, __DIR__.'migrations', 'migrations_version');
    }
}
