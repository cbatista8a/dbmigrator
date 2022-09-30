<?php

namespace CubaDevOps\DbMigrator\aplication;


use CubaDevOps\DbMigrator\domain\Configurator;
use CubaDevOps\DbMigrator\domain\interfaces\Migration;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\DatabaseRequired;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class Versioner
{
    /**
     * @var Finder
     */
    private Finder $file_manager;
    /**
     * @var Capsule
     */
    public Capsule $orm;
    /**
     * @var Configurator
     */
    private Configurator $configurator;

    public function __construct(Configurator $configurator)
    {
        $this->file_manager = new Finder();
        $this->configurator = $configurator;
        $this->orm = new Capsule;
        $this->orm->addConnection($this->configurator->getConnection()->toArray());
        $this->orm->setAsGlobal();
        $this->orm->bootEloquent();
        $this->installVersionTable();
    }


    /**
     * @return int
     */
    public function getLastVersion(): int
    {
        return (int)$this->orm::table($this->configurator->getTableVersionName())
            ->max('version');
    }

    /**
     * @return SplFileInfo[] | \Symfony\Component\Finder\SplFileInfo[]
     */
    public function loadMigrationFiles()
    {
        $this->file_manager->files()->name('*.php')->in($this->configurator->getMigrationPath());
        return $this->extractFileManagerFiles();
    }

    /**
     * @return SplFileInfo[] | \Symfony\Component\Finder\SplFileInfo[]
     */
    public function extractFileManagerFiles(): array
    {
        $files = [];
        if ($this->file_manager->hasResults()) {
            /** @var SplFileInfo $file */
            foreach ($this->file_manager as $file) {
                $files[] = $file;
            }
            $this->file_manager = new Finder();
        }
        return $files;
    }

    /**
     * @return SplFileInfo[] | \Symfony\Component\Finder\SplFileInfo[]
     */
    public function loadPendingMigrationFiles(): array
    {
        $migrations_filter = $this->executedMigrations();
        $this->file_manager->files()->name('*.php')
            ->notName($migrations_filter)
            ->in($this->configurator->getMigrationPath());
        return $this->extractFileManagerFiles();
    }

    /**
     * @return string[]
     */
    public function executedMigrations(): array
    {
        $migrations_obj = $this->orm::table($this->configurator->getTableVersionName())
            ->get();
        return $this->getFilePathsFromMigrationCollection($migrations_obj);
    }

    /**
     * @param Collection $migrations_obj
     * @return array
     */
    public function getFilePathsFromMigrationCollection(Collection $migrations_obj): array
    {
        $migrations = [];
        foreach ($migrations_obj as $migration) {
            $migrations[] = $migration->migration_path;
        }
        return $migrations;
    }

    /**
     * @param int $version
     * @return SplFileInfo[] | \Symfony\Component\Finder\SplFileInfo[]
     */
    public function loadForRollbackMigrationsFiles($version = 0): array
    {
        $migrations_obj = $this->getMigrationsByVersion($version);
        $migrations_filter = $this->getFilePathsFromMigrationCollection($migrations_obj);
        $this->file_manager->files()->name('*.php')
            ->name($migrations_filter)
            ->in($this->configurator->getMigrationPath());
        return $this->extractFileManagerFiles();
    }

    /**
     * @param int $version || 0 == all migrations
     * @return Collection
     */
    public function getMigrationsByVersion(int $version = 0): Collection
    {
        $builder = $this->orm::table($this->configurator->getTableVersionName());
        if ($version) {
            $builder->where('version', '=', $version);
        }
        return $builder->get();
    }

    /**
     * @param Migration $migration
     * @param SplFileInfo $file
     * @param int $version
     * @return bool
     */
    public function registerMigration($migration, $file, $version): bool
    {
        return $this->orm::table($this->configurator->getTableVersionName())
            ->insert(
                [
                    'version' => $version,
                    'migration_class' => get_class($migration),
                    'migration_path' => $file->getRealPath(),
                ]
            );
    }

    /**
     * @param SplFileInfo $file
     * @return bool
     */
    public function unregisterMigration($file): bool
    {
        return (bool)$this->orm::table($this->configurator->getTableVersionName())
            ->where('migration_path', '=', $file->getRealPath())
            ->delete();
    }

    /**
     *  Install the control version table
     * @throws DatabaseRequired
     */
    public function installVersionTable(): bool
    {
        if ($this->orm::schema()->hasTable($table_name = $this->configurator->getTableVersionName())) {
            return true;
        }
        try {
            $this->orm::schema()->create(
                $table_name,
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('version');
                    $table->string('migration_class');
                    $table->string('migration_path')->unique();
                    $table->timestamp('executed_at')
                        ->useCurrent()
                        ->useCurrentOnUpdate();
                }
            );
        } catch (\Exception $e) {
            throw new ConnectionException($e->getMessage(),$e->getCode());
        }
        return true;

    }

}