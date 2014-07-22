<?php
namespace CIC\Cicbase\Command;
class MigrationCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments
	 * @return void
	 */
	public function injectArguments(\TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments) {
		$this->arguments = $arguments;
	}


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
	 * Runs migrations for an extension. This is gonna be weird.
	 * @param string $extKey Key of extension containing migrations
	 * @return void
	 */
	public function runCommand($extKey) {
		$migrationRunner = $this->objectManager->get('CIC\\Cicbase\\Migration\\MigrationRunner');
		$this->outputLine();
		$messages = $migrationRunner->run($extKey);
		foreach($messages as $msg) {
			$this->outputLine($msg);
		}
		$this->outputLine();
	}

	/**
	 * Rolls back a migration.
	 * @param string $extKey Key of extension containing migrations
	 * @return void
	 */
	public function rollbackCommand($extKey) {
		$migrationRunner = $this->objectManager->get('CIC\\Cicbase\\Migration\\MigrationRunner');
		$this->outputLine();
		$messages = $migrationRunner->rollback($extKey);
		foreach($messages as $msg) {
			$this->outputLine($msg);
		}
		$this->outputLine();
	}
}
?>