# dbmigrator
Simple Control Version System for Databases

### How to Integrate on your Project or in a single module/plugin directory inside your project

First require autoload.php

`require_once './vendor/autoload.php';`

Then configure the connection to your database

`$connection = new Connection('localhost', '3306', 'root', 'root', 'test', 'mysql');`

Configure your migration path and the name of the migrations version table

`$configurator = new Configurator($connection, __DIR__.'/migrations_dir', 'migrations_version_table');`

Finally create the migrator manager

`$migrator = new Migrator($configurator);`

### How to use

First you must create a migration class that extend of Migration or just implement Migration interface if you use a simple plain query inside "up" and "down" methods

```php
use CubaDevOps\DbMigrator\domain\Migration;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class MigrationTest extends Migration
{

    public function up(): void
    {
        Manager::schema()->create('migration_example_table',function (Blueprint $table){
            $table->increments('id');
            $table->string('content')->default('test');
            // others columns
        });
    }

    public function down(): void
    {
        Manager::schema()->dropIfExists('migration_example_table');
    }
}
```
### How to Run Pending migrations

`$migrator->migrate();`

### How rollback migrations

`$migrator->rollback();`

### How to Run a Fresh version of your entire migrations

`$migrator->fresh();`

This is the same that do:

`$migrator->rollback();`
and then
`$migrator->migrate();`

You are welcome to contribute to this project with new features or bug fixes, just make a "pull request" on a new branch.
