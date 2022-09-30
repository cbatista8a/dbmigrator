<?php
namespace CubaDevOps\DbMigrator\domain\interfaces;

interface Migration
{
    public function up():void;
    public function down():void;
}