<?php
namespace CIC\Cicbase\Migration;

use CIC\Cicbase\Migration\Exception\MigrationFailureException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MigrationRunner {

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /** @var array */
    protected $messages = array();

    /** @var array */
    protected $stmts = array();

    /** @var string */
    protected $currentRunExtKey = null;

    /**
     * Some initializing here
     *
     * NOTE: Re-using prepared statements requires the parameters to be in a certain order:
     *       currently, 'version' must come before 'ext_key'.
     */
    public function initializeObject() {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $table = 'tx_cicbase_migrations';

        // Inserts and deletes don't really have prepared statements :(
        $deleteStmt = function(array $args) use ($table, $db) {
            $query = 'version = :version AND ext_key = :ext_key';
            foreach ($args as $key => $val) {
                $query = str_replace($key, $val, $query);
            }
            $db->exec_DELETEquery($table, $query);
        };
        $insertStmt = function(array $args) use ($table, $db) {
            $vals = array();
            foreach ($args as $key => $val) {
                $vals[str_replace(':', '', $key)] = $val;
            }
            $db->exec_INSERTquery($table, $vals);
        };


        $this->stmts = array(
            'lastRunMigration' => $db->prepare_SELECTquery('*', $table, 'ext_key = :ext_key', '', 'version DESC', 1),
            'insertMigration' => $insertStmt,
            'deleteMigration' => $deleteStmt,
            'findMigration' => $db->prepare_SELECTquery('*', $table, 'version = :version AND ext_key = :ext_key'),
        );
    }

    /**
     * @param $extKey
     * @return bool
     */
    public function hasMigrations($extKey) {
        return count(self::getAvailableMigrations($extKey)) > 0;
    }

    /**
     * @param string $extKey
     * @return array
     */
    public function run($extKey) {
        $this->reset($extKey);
        $this->messages[] = 'Running migrations for ' . $extKey . '...';
        $availableMigrations = self::getAvailableMigrations($extKey);
        try {
            foreach($availableMigrations as $migration) {
                $this->tryMigration($migration);
            }
        } catch (MigrationFailureException $migrationException) {
            $this->messages[] = 'Migration failure. Stopping all subsequent migrations';
        }

        return $this->messages;
    }

    /**
     * @param string $extKey
     * @return array
     */
    public function rollBack($extKey) {
        $this->reset($extKey);
        $migration = $this->getLastRunMigrationFor();
        if($migration == NULL) {
            $this->messageNoRollbackAvailable();
        } else {
            $this->tryRollback($migration);
        }
        return $this->messages;
    }

    /**
     * Gets a list of extensions that have migrations
     *
     * @return array
     */
    public static function migratableExtensions() {
        $allExts = ExtensionManagementUtility::getLoadedExtensionListArray();
        $exts = array();
        foreach ($allExts as $ext) {
            if (count(self::getAvailableMigrations($ext))) {
                $exts[] = $ext;
            }
        }
        return $exts;
    }

    public function migratableExtensionStatuses() {
        $extensions = self::migratableExtensions();
        $migrationStatuses = array();
        foreach ($extensions as $ext) {
            $migrations = self::getAvailableMigrations($ext);
            $migrationStatuses[$ext] = array();
            foreach ($migrations as $ts => $migrationName) {
                $rows = $this->dbQuery('findMigration', array(':version' => $ts, ':ext_key' => $ext));
                $migrationStatuses[$ext][$migrationName] = count($rows) ? 'completed' : 'pending';
            }
        }
        return $migrationStatuses;
    }

    /**
     * @return null
     */
    protected function getLastRunMigrationFor() {
        $rows = $this->dbQuery('lastRunMigration', array(':ext_key' => $this->currentRunExtKey));
        if(count($rows) == 1) {
            $version = $rows[0]['version'];
            return $this->getMigrationFromVersion($version);
        }
        return NULL;
    }



    /**
     * @param string $extKey
     */
    protected function reset($extKey) {
        $this->messages = array();
        $this->currentRunExtKey = $extKey;
    }

    /**
     *
     */
    protected function messageNoRollbackAvailable() {
        $this->messages[] = 'There are no migrations to rollback';
    }

    /**
     * @param $migration
     */
    protected function messageFailure($migration) {
        $this->messages[] = 'Failed to run migration: '.$migration;
    }

    /**
     * @param string $migration
     */
    protected function messageMigrationAlreadyRun($migration) {
        $this->messages[] = 'Already run: '.$migration;
    }

    /**
     * @param string $migration
     * @return bool|void
     */
    protected function tryMigration($migration) {
        $canRun = $this->checkMigrationState($migration);
        if($canRun == false) {
            $this->messageMigrationAlreadyRun($migration);
            return true;
        } else {
            return $this->runMigration($migration);
        }
    }

    /**
     * @param string $migration
     * @throws Exception\MigrationFailureException
     */
    protected function tryRollback($migration) {
        $migrationObject = $this->getMigrationObject($migration);
        if($migrationObject->canRollback()) {
            try {
                $migrationObject->rollBack();
                $this->handleMigrationRollbackSuccess($migration, $migrationObject);
            } catch (\Exception $e) {
                $this->handleMigrationRollbackFailure($migration, $migrationObject);
            }
        } else {
            $this->handleMigrationRollbackSuccess($migration, $migrationObject);
        }
    }

    /**
     * @param string $migration
     * @throws Exception\MigrationFailureException
     */
    protected function runMigration($migration) {
        $migrationObject = $this->getMigrationObject($migration);
        try {
            $migrationObject->run();
            if($GLOBALS['TYPO3_DB']->sql_error()) {
                throw new \Exception('SQL Error');
            } else {
                $this->handleMigrationSuccess($migration, $migrationObject);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() ."\n";
            $this->handleMigrationFailure($migration, $migrationObject);
        }
    }

    /**
     * @param string $migration
     * @param AbstractMigration $migrationObject
     * @throws Exception\MigrationFailureException
     */
    protected function handleMigrationRollbackFailure($migration, AbstractMigration $migrationObject) {
        $this->messages[] = $migration.' failed to rollback';
        $err = $migrationObject->getErrorMsg();
        if ($err) {
            $this->messages[] = "  ERROR: $err";
        }
        throw new MigrationFailureException;
    }

    /**
     * @param string $migration
     * @param AbstractMigration $migrationObject
     * @throws Exception\MigrationFailureException
     */
    protected function handleMigrationFailure($migration, AbstractMigration $migrationObject) {
        $this->messages[] = $migration.' failed to run';
        $err = $migrationObject->getErrorMsg();
        if ($err) {
            $this->messages[] = "  ERROR: $err";
        }
        throw new MigrationFailureException;
    }

    /**
     * @param string $migration
     * @param AbstractMigration $migrationObject
     * @return bool
     */
    protected function handleMigrationSuccess($migration, AbstractMigration $migrationObject) {
        $this->saveVersion($migration);
        $this->messages[] = $migration.' ran successfully';
        return true;
    }

    /**
     * @param string $migration
     * @param AbstractMigration $migrationObject
     * @return bool
     */
    protected function handleMigrationRollbackSuccess($migration, AbstractMigration $migrationObject) {
        $this->removeVersion($migration);
        $this->messages[] = $migration.' rolled back successfully';
        return true;
    }

    /**
     * @param string $migration
     */
    protected function saveVersion($migration) {
        $this->dbExec('insertMigration', array(
            ':version' => self::getTimestampFromMigration($migration),
            ':ext_key' => $this->currentRunExtKey
        ));
    }

    /**
     * @param string $migration
     */
    protected function removeVersion($migration) {
        $this->dbExec('deleteMigration', array(
            ':version' => self::getTimestampFromMigration($migration),
            ':ext_key' => $GLOBALS['TYPO3_DB']->fullQuoteStr($this->currentRunExtKey, ''),
        ));

    }

    /**
     * @param string $migrationName
     * @return AbstractMigration $migration
     */
    protected function getMigrationObject($migrationName) {
        $ext = ucfirst($this->currentRunExtKey);
        $vendorName = $this->getVendorName($ext) ?: 'CIC';
        return $this->objectManager->get("$vendorName\\$ext\\Migration\\$migrationName");
    }

    /**
     * A crude way to get the vendor name for an extension. This will likely need to be improved down the road.
     * @param $ext
     * @return mixed|string|null
     */
    protected function getVendorName($ext) {
        $package = $this->objectManager->get(PackageManager::class)->getPackage(strtolower($ext));
        if(!is_object($package)) return null;
        $autoload = $package->getValueFromComposerManifest('autoload');
        if(!is_object($autoload)) return null;
        $psr4 = get_object_vars($autoload)['psr-4'];
        if(!is_object($psr4)) return null;
        $entries = get_object_vars($psr4);
        if(!is_array($entries)) return null;
        $key = key($entries);
        return explode('\\', $key)[0];
    }


    /**
     * @param string $migration
     * @return bool True if can run
     */
    protected function checkMigrationState($migration) {
        $rows = $this->dbQuery('findMigration', array(
            ':version' => self::getTimestampFromMigration($migration),
            ':ext_key' => $this->currentRunExtKey,
        ));
        return !(count($rows) > 0);
    }

    /**
     * @param $stmtKey
     * @param $args
     * @return mixed
     */
    protected function dbQuery($stmtKey, $args) {
        return $this->dbExec($stmtKey, $args)->fetchAll();
    }

    /**
     * @param $stmtKey
     * @param $args
     * @return mixed
     */
    protected function dbExec($stmtKey, $args) {
        $stmt = $this->stmts[$stmtKey];
        if ($stmt instanceof \Closure) {
            $stmt($args);
            return $stmt;
        }
        $stmt->execute($args);
        return $stmt;
    }



    /**
     * @param string $version
     * @return null
     */
    protected function getMigrationFromVersion($version) {
        $migrations = self::getAvailableMigrations($this->currentRunExtKey);
        return isset($migrations[$version]) ? $migrations[$version] : NULL;
    }

    /**
     * @return array
     */
    protected static function getAvailableMigrations($extKey) {
        $basePath = ExtensionManagementUtility::extPath($extKey).'/Classes/Migration';
        $files = GeneralUtility::getFilesInDir($basePath);
        $classes = array();
        foreach($files as $file) {
            $ts = self::getTimestampFromMigration($file);
            if (!$ts) {
                continue;
            }
            $classes[$ts] = self::getMigrationNameFromFileName($file);
        }
        ksort($classes);
        return $classes;
    }

    /**
     * @param string $file
     * @return mixed
     */
    protected static function getMigrationNameFromFileName($file) {
        return str_replace('.php','',$file);
    }

    /**
     * @param string $migration
     * @return mixed
     */
    protected static function getTimestampFromMigration($migration) {
        return filter_var($migration, FILTER_SANITIZE_NUMBER_INT);
    }
}
