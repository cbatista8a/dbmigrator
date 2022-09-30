<?php
namespace CubaDevOps\DbMigrator\Tests;

use CubaDevOps\DbMigrator\domain\Configurator;
use CubaDevOps\DbMigrator\domain\Connection;
use CubaDevOps\DbMigrator\Migrator;
use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__,2).'/vendor/autoload.php';

class MigratorTest extends TestCase
{
    /**
     * @var Migrator
     */
    private Migrator $migrator;

    public function setUp(): void
    {
        $connection = new Connection('database', '3306', 'root', 'root', 'test', 'mysql');
        $configurator = new Configurator($connection, __DIR__.'/migrations_test', 'migrations_version');
        $this->migrator = new Migrator($configurator);
    }

    public function testRollback()
    {
        $this->migrator->rollback();
        self::assertFalse($this->migrator->versioner->orm::schema()->hasTable('migration_mock'));
    }

    /**
     * @depends testRollback
     */
    public function testMigrate()
    {
        $this->migrator->migrate();
        self::assertTrue($this->migrator->versioner->orm::schema()->hasTable('migration_mock'));
    }


    /**
     * @depends testMigrate
     */
    public function testFresh()
    {
        $this->migrator->fresh();
        self::assertTrue($this->migrator->versioner->orm::schema()->hasTable('migration_mock'));
    }
}
