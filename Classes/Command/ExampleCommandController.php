<?php
class Tx_Cicbase_Command_ExampleCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Example Command Controller
	 * @return void
	 */
	public function exampleCommand() {
		// Do stuff here!
		if (TYPO3_MODE == 'BE') {
			$message = $this->objectManager->get(
				't3lib_FlashMessage',
				'Now go make something cool.',
				'Awesome',
				t3lib_FlashMessage::OK
			);
			t3lib_FlashMessageQueue::addMessage($message);
		}
	}
}
?>