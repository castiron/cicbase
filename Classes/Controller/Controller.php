<?php
namespace CIC\Cicbase\Controller;

class Controller extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \CIC\Cicbase\Service\ControllerSecurityService
	 */
	protected $controllerSecurityService;

	/**
	 * Inject the controllerSecurityService
	 *
	 * @param \CIC\Cicbase\Service\ControllerSecurityService controllerSecurityService
	 * @return void
	 */
	public function injectControllerSecurityService(\CIC\Cicbase\Service\ControllerSecurityService $controllerSecurityService) {
		$this->controllerSecurityService = $controllerSecurityService;
	}

	/**
	 * Initialize the action method
	 */
	public function initializeAction() {
		// TODO Remove the cicblog stuff from cicbase
		// see https://www.pivotaltracker.com/story/show/73982204
		$this->controllerSecurityService->secureActionArguments($this->arguments, $this->request, 'Tx_Cicblog_Controller_PostsController');
	}


}

?>