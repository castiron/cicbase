<?php
namespace CIC\Cicbase\Command;
class MigrationCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \CIC\Cicbase\Migration\MigrationRunner
	 * @inject
	 */
	protected $runner;

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments
	 * @return void
	 */
	public function injectArguments(\TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * Runs migrations for an extension. This is gonna be weird.
	 * @param string $extKey Key of extension containing migrations
	 * @return void
	 */
	public function runCommand($extKey) {
		$this->outputLine();
		$messages = $this->runner->run($extKey);
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
		$this->outputLine();
		$messages = $this->runner->rollback($extKey);
		foreach($messages as $msg) {
			$this->outputLine($msg);
		}
		$this->outputLine();
	}
}
?>