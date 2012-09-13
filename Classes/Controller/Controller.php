<?php

class Tx_Cicbase_Controller_Controller extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Cicbase_Service_ControllerSecurityService
	 */
	protected $controllerSecurityService;

	/**
	 * Inject the controllerSecurityService
	 *
	 * @param Tx_Cicbase_Service_ControllerSecurityService controllerSecurityService
	 * @return void
	 */
	public function injectControllerSecurityService(Tx_Cicbase_Service_ControllerSecurityService $controllerSecurityService) {
		$this->controllerSecurityService = $controllerSecurityService;
	}

	/**
	 * Initialize the action method
	 */
	public function initializeAction() {
		$this->controllerSecurityService->secureActionArguments($this->arguments, $this->request, 'Tx_Cicblog_Controller_PostsController');
	}


}

?>