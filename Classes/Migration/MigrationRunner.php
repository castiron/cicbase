<?php
namespace CIC\Cicbase\Migration;
use \TYPO3\CMS\Core\Utility;

class MigrationRunner {

	/**
	 * @var array
	 */
	protected $messages = array();

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * inject the objectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @var string
	 */
	protected $currentRunExtKey = null;

	/**
	 * @param string $extKey
	 * @return array
	 */
	public function run($extKey) {
		$this->reset($extKey);
		$this->messages[] = 'Running migrations...';
		$availableMigrations = $this->getAvailableMigrations();
		try {
			foreach($availableMigrations as $migration) {
				$this->tryMigration($migration);
			}
		} catch (\CIC\Cicbase\Migration\Exception\MigrationFailureException $migrationException) {
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
	 * @return null
	 */
	protected function getLastRunMigrationFor() {
		$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'tx_cicbase_migrations', 'ext_key = :ext_key', '', 'version DESC', 1);
		$statement->execute(array(':ext_key' => $this->currentRunExtKey));
		$rows = $statement->fetchAll();
		if(count($rows) == 1) {
			$version = $rows[0]['version'];
			return $this->getMigrationFromVersion($version);
		} else {
			return null;
		}
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
	 * @param string $extKey
	 */
	protected function reset($extKey) {
		$this->messages = array();
		$this->currentRunExtKey = $extKey;
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
		$class = $this->getNamespacedClassnameFromMigrationName($migration);
		/** @var AbstractMigration $migrationObject */
		$migrationObject = $this->objectManager->get($class);
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
		$class = $this->getNamespacedClassnameFromMigrationName($migration);
		/** @var AbstractMigration $migrationObject */
		$migrationObject = $this->objectManager->get($class);
		try {
			$migrationObject->run();
			if($GLOBALS['TYPO3_DB']->sql_error()) {
				throw new \Exception('SQL Error');
			} else {
				$this->handleMigrationSuccess($migration, $migrationObject);
			}
		} catch (\Exception $e) {
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
		throw new \CIC\Cicbase\Migration\Exception\MigrationFailureException;
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
		throw new \CIC\Cicbase\Migration\Exception\MigrationFailureException;
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
		$GLOBALS['TYPO3_DB']->exec_insertQuery('tx_cicbase_migrations', array(
			'version' => $this->getTimestampFromMigration($migration),
			'ext_key' => $this->currentRunExtKey
		));
	}

	/**
	 * @param string $migration
	 */
	protected function removeVersion($migration) {
		$GLOBALS['TYPO3_DB']->exec_deleteQuery('tx_cicbase_migrations', 'ext_key = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->currentRunExtKey, '').' AND version = '.$this->getTimestampFromMigration($migration));
	}

	/**
	 * @param string $migration
	 * @return string
	 */
	protected function getNamespacedClassnameFromMigrationName($migration) {
		return 'CIC\\'.ucfirst($this->currentRunExtKey).'\\Migration\\'.$migration;
	}

	/**
	 * @param string $migration
	 * @return bool
	 */
	protected function checkMigrationState($migration) {
		$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'tx_cicbase_migrations', 'ext_key = :ext_key AND version = :version');
		$statement->execute(array(':ext_key' => $this->currentRunExtKey, ':version' => $this->getTimestampFromMigration($migration)));
		$rows = $statement->fetchAll();
		$count = count($rows);
		if($count > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param string $file
	 * @return mixed
	 */
	protected function getClassNameFromFileName($file) {
		$className = str_replace('.php','',$file);
		return $className;
	}

	/**
	 * @return array
	 */
	protected function getAvailableMigrations() {
		$basePath = Utility\extensionManagementUtility::extPath($this->currentRunExtKey).'/Classes/Migration';
		$files = Utility\GeneralUtility::getFilesInDir($basePath);
		$classes = array();
		foreach($files as $file) {
			$classes[] = $this->getClassNameFromFileName($file);
		}
		$classes = $this->sortMigrations($classes);
		return $classes;
	}

	/**
	 * @param string $version
	 * @return null
	 */
	protected function getMigrationFromVersion($version) {
		$migrations = $this->getAvailableMigrations();
		return isset($migrations[$version]) ? $migrations[$version] : NULL;
	}

	/**
	 * @param string $migration
	 * @return mixed
	 */
	protected function getTimestampFromMigration($migration) {
		$migration = filter_var($migration,FILTER_SANITIZE_NUMBER_INT);
		return $migration;
	}

	/**
	 * @param string $migrations
	 * @return array
	 */
	protected function sortMigrations($migrations) {
		$sorted = array();
		foreach($migrations as $migration) {
			$sorted[$this->getTimestampFromMigration($migration)] = $migration;
		}
		ksort($sorted);
		return $sorted;
	}
}

?>