<?php

class Tx_Cicbase_ViewHelpers_Widget_Controller_FileUploadController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	public function initializeIndexAction() {
		Tx_Extbase_Utility_Debugger::var_dump($this->arguments['file']);
		Tx_Extbase_Utility_Debugger::var_dump($_POST);
		$resolutionMappingConfig = $this->arguments['file']->getPropertyMappingConfiguration();
		$resolutionMappingConfig->setTypeConverter($this->objectManager->get('\CIC\Cicbase\Property\TypeConverter\FileReferenceConverter'));
		$resolutionMappingConfig->setTypeConverterOption('\CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'propertyPath', 'file');
		$resolutionMappingConfig->setTypeConverterOption('\CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'maxSize', 20971520);
		$resolutionMappingConfig->setTypeConverterOption('\CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'allowedTypes', array(
			'pdf' => 'application/pdf',
			'gif' => 'image/gif',
			'jpg' => 'image/jpg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'html' => 'text/html',
			'htm' => 'text/html',
			'txt' => 'text/plain',
			'xml' => 'text/xml'
		));
	}

	public function indexAction() {

	}

}

?>