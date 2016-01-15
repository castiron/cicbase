<?php
namespace CIC\Cicbase\Command;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $this->runExtension($extKey);
		$this->outputLine();
	}

    /**
     * @param $extKey
     */
    protected function runExtension($extKey) {
        $messages = $this->runner->run($extKey);
        $this->putMessages($messages);
    }

    /**
     * @param $extKey
     */
    protected function rollbackExtension($extKey) {
        $messages = $this->runner->rollback($extKey);
        $this->putMessages($messages);
    }

    /**
     * @param $messages
     */
    protected function putMessages($messages) {
        foreach($messages as $msg) {
            $this->outputLine($msg);
        }
    }

	/**
	 * Rolls back a migration.
	 * @param string $extKey Key of extension containing migrations
	 * @return void
	 */
	public function rollbackCommand($extKey) {
		$this->outputLine();
        $this->rollbackExtension($extKey);
		$this->outputLine();
	}

    /**
     *
     */
    public function runAllCommand() {
        $keys = GeneralUtility::get_dirs(PATH_site . 'typo3conf/ext');
        foreach ($keys as $extKey) {
            if (ExtensionManagementUtility::isLoaded($extKey)
                && $this->runner->hasMigrations($extKey)) {
                $this->runExtension($extKey);
                $this->outputLine();
            }
        }
    }

    /**
     *
     */
    public function rollbackAllCommand() {
        $keys = GeneralUtility::get_dirs(PATH_site . 'typo3conf/ext');
        foreach ($keys as $extKey) {
            if (ExtensionManagementUtility::isLoaded($extKey)) {
                $this->rollbackExtension($extKey);
                $this->outputLine();
            }
        }
    }
}
