<?php

namespace CIC\Cicbase\ViewHelpers\Widget\Controller;

class FileUploadController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	public function initializeIndexAction() {
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->arguments['file']);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($_POST);
		$resolutionMappingConfig = $this->arguments['file']->getPropertyMappingConfiguration();
		$resolutionMappingConfig->setTypeConverter($this->objectManager->get('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter'));
		$resolutionMappingConfig->setTypeConverterOption('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'propertyPath', 'file');
		$resolutionMappingConfig->setTypeConverterOption('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'maxSize', 20971520);
		$resolutionMappingConfig->setTypeConverterOption('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter', 'allowedTypes', array(
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