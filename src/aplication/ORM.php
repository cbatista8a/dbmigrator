<?php


namespace CubaDevOps\DbMigrator\aplication;


use CubaDevOps\DbMigrator\domain\interfaces\DBConnection;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;

class ORM extends Manager
{
    public function __construct(DBConnection $connection ,Container $container = null)
    {
        parent::__construct($container);
        $this->addConnection($connection->toArray());
        $this->setAsGlobal();
        $this->bootEloquent();
    }
}