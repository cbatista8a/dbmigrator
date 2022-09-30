<?php
namespace CubaDevOps\DbMigrator\domain;

use CubaDevOps\DbMigrator\domain\exceptions\FileExistsException;
use CubaDevOps\DbMigrator\domain\interfaces\DBConnection;

class Configurator
{
    private $connection;
    private $migration_path;
    private $table_version_name;

    public function __construct(DBConnection $connection,string $migration_path,string $table_version_name = 'migrations_version')
    {
        $this->connection = $connection;
        $this->setTableVersionName($table_version_name);
        $this->setPath($migration_path);
    }

    /**
     * @return DBConnection
     */
    public function getConnection(): DBConnection
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getMigrationPath()
    {
        return $this->migration_path;
    }

    /**
     * @return string
     */
    public function getTableVersionName()
    {
        return $this->table_version_name;
    }

    /**
     * @param string $migration_path
     * @throws FileExistsException
     */
    public function setPath(string $migration_path): Configurator
    {
        if (file_exists($migration_path)) {
            $this->migration_path = $migration_path;
        } else {
            throw new FileExistsException('The migration path don\'t exist or you are wrong permissions');
        }
        return $this;
    }

    /**
     * @param string $table_version_name
     */
    public function setTableVersionName(string $table_version_name): Configurator
    {
        $this->table_version_name = $table_version_name;
        return $this;
    }
}