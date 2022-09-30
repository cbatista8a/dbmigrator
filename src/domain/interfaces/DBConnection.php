<?php
namespace CubaDevOps\DbMigrator\domain\interfaces;


interface DBConnection
{
    public function getHost():string;
    public function getPort():string;
    public function getUsername():string;
    public function getPassword():string;
    public function getDatabaseName():string;
    public function getDriver():string;
    public function toArray():array;
}