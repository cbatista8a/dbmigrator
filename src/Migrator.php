<?php
namespace CubaDevOps\DbMigrator;

use CubaDevOps\DbMigrator\aplication\Versioner;
use CubaDevOps\DbMigrator\domain\Configurator;
use CubaDevOps\DbMigrator\domain\exceptions\MigrationExecuteException;
use CubaDevOps\DbMigrator\domain\interfaces\Migration;
use Exception;
use PhpToken;
use SplFileInfo;

class Migrator
{
    /**
     * @var Versioner
     */
    public Versioner $versioner;
    public int $current_version;

    public function __construct(Configurator $configurator)
    {
        $this->versioner = new Versioner($configurator);
        $this->current_version = $this->versioner->getLastVersion();
    }

    public function migrate(): void
    {
        $next_version = $this->current_version + 1;
        $files = $this->versioner->loadPendingMigrationFiles();
        foreach ($files as $file){
            /** @var string class */
            $class = $this->getAbsoluteClassNameFromFile($file);
            $this->executeUp(new $class, $file, $next_version);
        }
        unset($migration);
    }

    public function rollback(bool $latest_version = true): void
    {
        $version = $latest_version ? $this->current_version : 0;
        $files = $this->versioner->loadForRollbackMigrationsFiles($version);
        foreach ($files as $file){
            /** @var string class */
            $class = $this->getAbsoluteClassNameFromFile($file);
            $this->executeDown(new $class, $file);
        }
        unset($migration);
    }

    public function fresh(): void
    {
        $this->rollback(false);
        $this->current_version = 0;
        $this->migrate();
    }


    /**
     * @param Migration $migration
     * @param SplFileInfo $file
     * @param int $version
     * @throws MigrationExecuteException
     */
    public function executeUp($migration,$file, $version): void
    {
        try {
            $migration->up();
            $this->versioner->registerMigration($migration,$file, $version);
        }catch (Exception $e){
            throw new MigrationExecuteException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @param Migration $migration
     * @param SplFileInfo $file
     * @throws MigrationExecuteException
     */
    public function executeDown($migration, $file): void
    {
        try {
            $migration->down();
            $this->versioner->unregisterMigration($file);
        }catch (Exception $e){
            throw new MigrationExecuteException($e->getMessage(),$e->getCode());
        }

    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @return string
     */
    private function getAbsoluteClassNameFromFile($file)
    {
        $tokens = PhpToken::tokenize($file->getContents());
        $namespace = [];
        foreach ($tokens as $index => $token) {
            if ($token->is(T_NAMESPACE) && $tokens[$index+2]->is(T_STRING)){
                for ($i = $index+2 ;!$tokens[$i]->is(T_WHITESPACE);$i++){
                    if ($tokens[$i]->text === ";"){
                        continue;
                    }
                    $namespace[] = $tokens[$i]->text;
                }
                unset($tokens);
                return implode('',$namespace)."\\".$file->getFilenameWithoutExtension();
            }
        }
        return "\\".$file->getFilenameWithoutExtension();
    }
}