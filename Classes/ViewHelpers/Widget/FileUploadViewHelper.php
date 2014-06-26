<?php

namespace CIC\Cicbase\ViewHelpers\Widget;

class FileUploadViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @var \CIC\Cicbase\ViewHelpers\Widget\Controller\FileUploadController
	 */
	protected $controller;
	
	/**
	 * inject the controller
	 *
	 * @param \CIC\Cicbase\ViewHelpers\Widget\Controller\FileUploadController controller
	 * @return void
	 */
	public function injectController(\CIC\Cicbase\ViewHelpers\Widget\Controller\FileUploadController $controller) {
		$this->controller = $controller;
	}
	
	/**
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $objects
	 * @param string $as
	 * @param array $configuration
	 * @return string
	 */
	public function render() {
		return $this->initiateSubRequest();
	}


}

?>