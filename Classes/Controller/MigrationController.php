<?php
namespace CIC\Cicbase\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;

class MigrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \CIC\Cicbase\Migration\MigrationRunner
	 * @inject
	 */
	protected $runner;

	/**
	 * Show the extensions to migrate
	 */
	public function indexAction() {
		$exts = $this->runner->migratableExtensions();
		$this->view->assign('extensions', array_combine($exts, $exts));
	}

	/**
	 * @param string $extension
	 * @param string $process
	 */
	public function runAction($extension, $process) {
		$messages = $this->runner->$process($extension);
		$q = $this->controllerContext->getFlashMessageQueue();
		foreach ($messages as $message) {
			$q->enqueue(new FlashMessage($message, '', FlashMessage::INFO));
		}
		$this->forward('index');
	}


}

?>