<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Tx_Cicbase_Factory_FileFactory implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * A list of property mapping messages (errors, warnings) which have occured on last mapping.
	 *
	 * @var Tx_Extbase_Error_Result
	 */
	protected $messages;

	/**
	 * @var string
	 */
	protected $propertyPath = '';

	/**
	 * @var Tx_Cicbase_Domain_Repository_FileRepository
	 */
	protected $fileRepository;

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * inject the persistenceManager
	 *
	 * @param Tx_Extbase_Persistence_Manager persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * inject the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManager configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * inject the objectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * inject the fileRepository
	 *
	 * @param Tx_Cicbase_Domain_Repository_FileRepository fileRepository
	 * @return void
	 */
	public function injectFileRepository(Tx_Cicbase_Domain_Repository_FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
	}

	/**
	 * Returns the current plugin namespace
	 * @return string
	 */
	protected function getNamespace() {
		$framework = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$extension = $framework['extensionName'];
		$plugin = $framework['pluginName'];
		$namespace = 'tx_' . strtolower($extension) . '_' . strtolower($plugin);
		return $namespace;
	}

	public function wasUploadAttempted($propertyPath) {
		$fileData = array();
		$fd = $_FILES[$this->getNamespace()];
		$key = $propertyPath . '.file';
		if
			($fd['error'][$key] != 4 &&
			(
				$fd['error'][$key] ||
				$fd['size'][$key] ||
				$fd['tmp_name'][$key]
			)
		) {
			return true;
		} else {
			return false;
		}
	}

	protected function getUploadedFileData() {
		$fileData = array();
		$fd = $_FILES[$this->getNamespace()];
		$fileData['error'] = $fd['error'][$this->propertyPath . '.file'];
		$fileData['type'] = $fd['type'][$this->propertyPath . '.file'];
		$fileData['name'] = $fd['name'][$this->propertyPath . '.file'];
		$fileData['size'] = $fd['size'][$this->propertyPath . '.file'];
		$fileData['tmp_name'] = $fd['tmp_name'][$this->propertyPath . '.file'];
		return $fileData;
	}

	protected function handleUploadErrors($uploadedFileData) {
		if($uploadedFileData['error']) {
			switch ($uploadedFileData['error']) {
				case 1:
				case 2:
					$this->addError('File exceeds upload size limit', 1336597081);
				break;
				case 3:
					$this->addError('File was only partially uploaded. Please try again', 1336597082);
				break;
				case 4:
					$this->addError('No file was uploaded.', 1336597083);
				break;
				case 5:
				case 6:
				case 7:
					$this->addError('Bad destination error.', 1336597084);
				break;
				default:
					$this->addError('Unknown error.', 1336597085);
				break;
			}
		} else {
			return NULL;
		}
	}

	protected function validateType($uploadedFileData,$allowedTypes) {
		$pathInfo = pathinfo($uploadedFileData['name']);
		$extension = $pathInfo['extension'];
		if($allowedTypes[$extension] == $uploadedFileData['type']) {
			return NULL;
		} else {
			$this->addError('Invalid mime type', 1336597086);
		}
	}

	protected function addError($msg, $key) {
		$error = new Tx_Extbase_Error_Error($msg, $key);
		$this->messages->addError($error);
	}

	protected function validateName($uploadedFileData) {
		return NULL;
	}

	protected function validateSize($uploadedFileData,$maxSize) {
		if($uploadedFileData['size'] > $maxSize) {
			$this->addError('Uploaded file size ('.$uploadedFileData['size'].') exceeds max allowed size', 1336597087);
		} else {
			return NULL;
		}
	}

	public function createFile(array $sourceData, $propertyPath, $allowedTypes, $maxSize) {
		$this->messages = new Tx_Extbase_Error_Result();
		$this->propertyPath = $propertyPath;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

		$uploadedFileData = $this->getUploadedFileData();
		$isUploadError = $this->handleUploadErrors($uploadedFileData);
		if($this->messages->hasErrors()) {
			return $this->messages->getFirstError();
		} else {
			$this->validateType($uploadedFileData,$allowedTypes);
			$this->validateName($uploadedFileData);
			$this->validateSize($uploadedFileData,$maxSize);
		}

		if($this->messages->hasErrors()) {
			return $this->messages->getFirstError();
		} else {
			// ok to make a file object
			$pathInfo = pathinfo($uploadedFileData['tmp_name']);
			$fileObject = $this->objectManager->create('Tx_Cicbase_Domain_Model_File');
			$fileObject->setTitle($sourceData['title']);

			// TODO: Set a default title if it's not provided.
			$fileObject->setDescription($sourceData['description']);
			$fileObject->setIsSaved(false);
			$fileObject->setOwner($GLOBALS['TSFE']->fe_user->user['uid']);
			$fileObject->setSize($uploadedFileData['size']);
			$fileObject->setMimeType($uploadedFileData['type']);
			$fileObject->setOriginalFilename($uploadedFileData['name']);
			$fileObject->setPath($uploadedFileData['tmp_name']);
			$fileObject->setFilename($pathInfo['filename']);
			Tx_Extbase_Utility_Debugger::var_dump('in here',__FILE__ . " " . __LINE___);
			$this->fileRepository->add($fileObject);
			$this->persistenceManager->persistAll();
			return $fileObject;
		}
	}

	/**
	 * Grabs string values from the locallang.xml file.
	 *
	 * @static
	 * @param string $string The name of the key in the locallang.xml file.
	 * @return string The value of that key
	 */
	protected static function translate($string) {
		return htmlspecialchars(Tx_Extbase_Utility_Localization::translate('tx_sjcert_domain_model_municipalityclaim.' . $string, 'sjcert'));
	}

}

?>