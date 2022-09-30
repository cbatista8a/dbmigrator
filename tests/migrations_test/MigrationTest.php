<?php


namespace CubaDevOps\DbMigrator\Tests\migrations_test;


use CubaDevOps\DbMigrator\domain\Migration;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class MigrationTest extends Migration
{

    /**
     *
     */
    public function up(): void
    {
        Manager::schema()->create('migration_mock',function (Blueprint $table){
            $table->increments('id');
            $table->string('content')->default('test');
        });
    }

    /**
     *
     */
    public function down(): void
    {
        Manager::schema()->dropIfExists('migration_mock');
    }
}