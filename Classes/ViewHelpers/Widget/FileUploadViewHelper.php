<?php

class Tx_Cicbase_ViewHelpers_Widget_FileUploadViewHelper extends Tx_Fluid_Core_Widget_AbstractWidgetViewHelper {

	/**
	 * @var Tx_Cicbase_ViewHelpers_Widget_Controller_FileUploadController
	 */
	protected $controller;
	
	/**
	 * inject the controller
	 *
	 * @param Tx_Cicbase_ViewHelpers_Widget_Controller_FileUploadController controller
	 * @return void
	 */
	public function injectController(Tx_Cicbase_ViewHelpers_Widget_Controller_FileUploadController $controller) {
		$this->controller = $controller;
	}
	
	/**
	 *
	 * @param Tx_Extbase_Persistence_QueryResultInterface $objects
	 * @param string $as
	 * @param array $configuration
	 * @return string
	 */
	public function render() {
		return $this->initiateSubRequest();
	}


}

?>