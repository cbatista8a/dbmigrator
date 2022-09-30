<?php


namespace CubaDevOps\DbMigrator\domain;

use CubaDevOps\DbMigrator\domain\interfaces\Migration as IMigration;
//use Illuminate\Database\Capsule\Manager;
use \Illuminate\Database\Migrations\Migration as MigrationBase;

abstract class Migration extends MigrationBase implements IMigration
{
    public $withinTransaction = true;
    /*
     * Use Manager::schema()->
     * for build tables and columns
     * */
}