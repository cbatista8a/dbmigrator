<?php

namespace CubaDevOps\DbMigrator\Tests\aplication;

use CubaDevOps\DbMigrator\aplication\Versioner;
use CubaDevOps\DbMigrator\domain\Configurator;
use CubaDevOps\DbMigrator\domain\Connection;
use CubaDevOps\DbMigrator\Tests\migrations_test\MigrationTest;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class VersionerTest extends TestCase
{
    private Connection $connection;
    private Configurator $configurator;
    private Versioner $versioner;
    private SplFileInfo $file;

    public function __construct()
    {
        parent::__construct();
        $this->connection = new Connection('server', '3306', 'root', 'root', 'test', 'mysql');
        $file_path = dirname(__FILE__, 2) . '/migrations_test/MigrationTest.php';
        $this->file = new SplFileInfo($file_path);
    }

    public function setUp(): void
    {
        $this->configurator = new Configurator($this->connection, __DIR__, 'migrations_version');
        $this->versioner = new Versioner($this->configurator);
    }

    /**
     * @throws \Doctrine\DBAL\Exception\DatabaseRequired
     */
    public function testInstallVersionTable()
    {
        $this->versioner->installVersionTable();
        $this->assertTrue($this->versioner->orm::schema()->hasTable('migrations_version'));
    }

    /**
     * @depends testInstallVersionTable
     */
    public function testUnregisterMigration()
    {
        $this->versioner->unregisterMigration($this->file);
        $this->assertFalse($this->versioner->orm::table('migrations_version')
                               ->where('migration_path','=',$this->file->getRealPath())
                               ->exists()
        );
    }

    /**
     * @depends testUnregisterMigration
     */
    public function testRegisterMigration()
    {
        $this->versioner->registerMigration(new MigrationTest(), $this->file, 1);
        $this->assertEquals($this->file->getRealPath(),
                            $this->versioner->orm::table("migrations_version")
            ->where('migration_path','=',$this->file->getRealPath())
            ->first()->migration_path
        );
    }


}
