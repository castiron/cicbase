<?php
namespace CIC\Cicbase\Command;

class ExampleCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * Example Command Controller
	 * @return void
	 */
	public function exampleCommand() {
		// Do stuff here!
		if (TYPO3_MODE == 'BE') {
			$message = $this->objectManager->get(
				'TYPO3\CMS\Core\Messaging\FlashMessage',
				'Now go make something cool.',
				'Awesome',
				\TYPO3\CMS\Core\Messaging\FlashMessage::OK
			);
			\TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
		}
	}
}
?>