<?php
namespace CIC\Cicbase\Factory;
use CIC\Cicbase\Domain\Model\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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

class FileReferenceFactory implements \TYPO3\CMS\Core\SingletonInterface {


	/** @var string  */
	protected $storagePath = 'cicbase/uploads/';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * A list of property mapping messages (errors, warnings) which have occurred on last mapping.
	 *
	 * @var \TYPO3\CMS\Extbase\Validation\Error
	 */
	protected $messages;

	/**
	 * @var string
	 */
	protected $propertyPath = '';

	/**
	 * @var \CIC\Cicbase\Persistence\Limbo
	 * @inject
	 */
	protected $limbo;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 * @inject
	 */
	protected $fileFactory;

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 * @inject
	 */
	protected $fileRepository;

	/**
	 * @var \TYPO3\CMS\Core\Resource\StorageRepository
	 * @inject
	 */
	protected $folderRepository;

	/**
	 * @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility
	 * @inject
	 */
	protected $fileUtility;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper;

	/**
	 * Inject the configuration manager
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}


	/**
	 * @param string $propertyPath
	 * @param array $additionalReferenceProperties
	 * @param array $allowedTypes
	 * @param int $maxSize
	 * @return \TYPO3\CMS\Extbase\Error\Error
	 */
	public function createFileReference($propertyPath, $additionalReferenceProperties, $allowedTypes, $maxSize) {
		$this->messages = new \TYPO3\CMS\Extbase\Error\Result();
		$key = $propertyPath ? $propertyPath : '';

		$uploadedFileData = $this->getUploadedFileData($propertyPath);
		$this->handleUploadErrors($uploadedFileData);

		if($this->messages->hasErrors()) {
			$this->limbo->clearHeld($key);
			return $this->messages->getFirstError();
		} else {
			$propertyPathUnderscores = $propertyPath ? str_replace('.', '_', $propertyPath) : 'file';
			if(!$this->settings['files']['dontValidateMime'][$propertyPathUnderscores]) {
				$this->validateType($uploadedFileData, $allowedTypes);
			}
			if(!$this->settings['files']['dontValidateSize'][$propertyPathUnderscores]) {
				$this->validateSize($uploadedFileData, $maxSize);
			}
		}

		if($this->messages->hasErrors()) {
			$this->limbo->clearHeld($key);
			return $this->messages->getFirstError();
		} else {
			$fileReference = $this->buildFileReference($propertyPath, $additionalReferenceProperties);

			$this->limbo->hold($fileReference, $key);

			return $fileReference;
		}
	}


	/**
	 * Checks for errors in $_FILES array
	 *
	 * @param $propertyPath
	 * @return bool
	 */
	public function wasUploadAttempted($propertyPath) {
		$data = $this->getUploadedFileData($propertyPath);
		return $data['error'] != 4 && ($data['error'] || $data['size'] || $data['tmp_name']);
	}


	/**
	 * @param string $propertyPath
	 * @return \CIC\Cicbase\Domain\Model\FileReference
	 */
	public function getHeldFileReference($propertyPath) {
		return $this->limbo->getHeld($propertyPath);
	}


	/**
	 * @param string $propertyPath
	 * @param null $additionalReferenceProperties
	 * @return \CIC\Cicbase\Domain\Model\FileReference
	 */
	protected function buildFileReference($propertyPath, $additionalReferenceProperties = NULL) {
		$pathExists = $this->temporaryPathExists();
		$relFolderPath = $this->buildTemporaryPath(FALSE);

		$folder = $this->createFolderObject($relFolderPath, $pathExists);

		// Folder data from $_FILES
		$source = $this->getUploadedFileData($propertyPath);

		// Use the file hash as the name of the file
		$originalFilename = $source['name'];
		$source['name'] = md5_file($source['tmp_name']) . '.' . pathinfo($source['name'], PATHINFO_EXTENSION);

		// Create the FileObject by adding the uploaded file to the FolderObject.
		$file = $folder->addUploadedFile($source, 'replace');

		// Default properties for our reference object from our File object.
		$referenceProperties = array(
			'uid_local' => $file->getUid(),
			'table_local' => 'sys_file',
			'title' => $originalFilename,
		);

		// Allow for additional reference properties to be added
		if (is_array($additionalReferenceProperties)) {
			$referenceProperties = array_merge($referenceProperties, $additionalReferenceProperties);
		}

		// Build a FileReference object using our reference properties
		$ref = $this->fileFactory->createFileReferenceObject($referenceProperties);

		// Convert the Core FileReference we made to an CICBase FileReference
		$fileReference = $this->objectManager->getEmptyObject('CIC\Cicbase\Domain\Model\FileReference');
		$fileReference->setOriginalResource($ref);
		return $fileReference;

	}

	/**
	 * This is the last step of the FileReference creation process.
	 * It should be called when the file is ready to be saved
	 * permanently. At this point, the file will be moved from
	 * a temporary location to a permanent location.
	 *
	 * @param FileReference $fileReference
	 * @param string $key
	 */
	protected function save(FileReference &$fileReference, $key = '') {
		if ($fileReference->getUid()) {
			return;
		}
		/** @var \TYPO3\CMS\Dbal\Database\DatabaseConnection $db */
		$db = $GLOBALS['TYPO3_DB'];

		$pathExists = $this->permanentPathExists();
		$relFolderPath = $this->buildPermanentPath(FALSE);

		$folder = $this->createFolderObject($relFolderPath, $pathExists);

		$ref = $fileReference->getOriginalResource();
		$file = $ref->getOriginalFile();
		$fileName = $file->getName();

		$existingFile = $db->exec_SELECTgetSingleRow('uid', 'sys_file', "name = '$fileName' AND identifier NOT LIKE '/_temp_/%' AND deleted = 0");
		if (is_array($existingFile)) {

			// Update the file reference to use the existing file
			$fileReference->setUidLocal($existingFile['uid']);
			$fileReferenceData = $fileReference->toArray();
			$coreReference = new \TYPO3\CMS\Core\Resource\FileReference($fileReferenceData);
			$fileReference->setOriginalResource($coreReference);

			// Remove the temp file
			try {

				$id = $file->getIdentifier();
				if (strpos($id, $this->storagePath) != 1) { // i.e. "/cicbase/uploads/2014..."
					$file->delete();
					$db->exec_DELETEquery('sys_file', "identifier = '$id'");
				}
			} catch (\Exception $e) {
				// It may have been deleted before if another upload of the same file was in progress.
			}
		} else {
			$file->moveTo($folder, $file->getName(), 'replace');
		}

		$this->limbo->clearHeld($key);
	}


	/**
	 *
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object
	 * @param string $fieldname
	 * @param \Iterator $fileReferences
	 * @param string $key
	 * @throws \Exception
	 */
	public function saveAll(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object, $fieldname, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $fileReferences, $key = '') {

		$someSavedAlready = FALSE;
		$savableReferences = array();
		foreach ($fileReferences as $ref) {
			if (!$ref instanceof FileReference) {
				$fileReferences->detach($ref);
				continue;
			}
			$savableReferences[] = $ref;
			$uid = $ref->getUid();
			if ($uid) {
				$someSavedAlready = TRUE;
				$keepers[] = $uid;
			}
		}

		// This is a new $object, unlikely to have any existing FileReferences for this field.
		if (!$object->getUid() || !$someSavedAlready) {
			foreach ($savableReferences as $ref) {
				$this->save($ref, $key);
			}
			return;
		}

		// Remove FileReferences for this field, unless we're keeping them.
		$datamap = $this->dataMapper->getDataMap(get_class($object));
		$this->removeFileReferences($object->getUid(), $datamap->getTableName(), $fieldname, $keepers);

		// Save any new ones
		$i = 0;
		foreach($savableReferences as $ref) {
			$this->save($ref, $key);
			// We need to do this because, no matter which reference
			// is loaded, we need to clear the them all. And $this->save()
			// doesn't do that exactly.
			$this->limbo->clearHeld($key.'.'.$i++);
		}
	}



	/**
	 * Since ExtBase isn't forcing the one to one relationships
	 * for FileReferences, we're doing it here.
	 *
	 * ASSUMPTION: FileReference to File is one to one as well.
	 * Meaning, deleting a FileReference will delete the File.
	 *
	 * NOTE: At this point, the FileReference object has not been saved
	 * in the database.
	 *
	 * @param FileReference $fileReference
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object
	 * @param string $fieldname
	 * @param string $propertyPath
	 */
	public function saveOneToOne(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object, $fieldname, FileReference $fileReference, $propertyPath = 'file' ) {
		// This is a new $object, unlikely to have any existing FileReferences for this field.
		if (!$object->getUid() || $fileReference->getUid()) {
			return $this->save($fileReference, $propertyPath);
		}

		// Remove all FileReferences for this field
		$datamap = $this->dataMapper->getDataMap(get_class($object));
		$this->removeFileReferences($object->getUid(), $datamap->getTableName(), $fieldname);

		return $this->save($fileReference, $propertyPath);
	}


	/**
	 * This function removes all FileReference records
	 * with the given arguments, except the keeper UIDs.
	 *
	 * @param integer $uidForeign
	 * @param string $tablenames
	 * @param string $fieldname
	 * @param array $keepers UIDs
	 */
	protected function removeFileReferences($uidForeign, $tablenames, $fieldname, $keepers = array()) {
		/** @var \TYPO3\CMS\Dbal\Database\DatabaseConnection $db */
		$db = $GLOBALS['TYPO3_DB'];
		$refTable = 'sys_file_reference';
		$fileTable = 'sys_file';
		$filesToDelete = array();
		$refUids = array();
		$fileUids = array();

		// Clauses
		$select = "$refTable.uid, $refTable.uid_local, $refTable.tablenames, $refTable.uid_foreign, $refTable.fieldname, $fileTable.identifier";
		$identifyingClauses = "$refTable.tablenames = '$tablenames' AND $refTable.uid_foreign = $uidForeign AND $refTable.fieldname = '$fieldname'";
		$where = "$identifyingClauses AND $fileTable.uid = $refTable.uid_local";
		if ($keepers) {
			$where .= " AND $refTable.uid NOT IN (". implode(',', $keepers).")";
		}

		// Consolidate Files and FileReferences to delete
		$rows = $db->exec_SELECTgetRows($select, "$refTable, $fileTable", $where);
		foreach ($rows as $row) {
			$refUids[] = $row['uid'];
			$filesToDelete[$row['uid_local']] = $row['identifier'];
		}

		// Don't delete File objects if there are other references to them
		// Don't unlink the actual file if there is another file with the same identifier
		// (which shouldn't happen anyway because we try to re-use files)
		$refUidsClause = implode(',', $refUids);
		foreach ($filesToDelete as $fileUid => $filePath) {
			$refCount = $db->exec_SELECTcountRows('uid', $refTable, "$refTable.uid_local = $fileUid AND $refTable.uid NOT IN ($refUidsClause) ");
			if (!$refCount) {
				$fileUids[] = $fileUid;

				$fileCount = $db->exec_SELECTcountRows('uid', $fileTable, "$fileTable.identifier = '$filePath' AND $fileTable.uid != $fileUid");
				if (!$fileCount) {
					unlink(PATH_site.'fileadmin/'.$filePath);
				}
			}
		}

		// Ok to remove these now
		if (count($refUids)) {
			$db->exec_DELETEquery($refTable, "uid IN ($refUidsClause)");
		}

		if (count($fileUids)) {
			$db->exec_DELETEquery($fileTable, "uid IN (".implode(',', $fileUids).")");
		}
	}

	/**
	 * @param bool $absolute
	 * @param bool $mkdir
	 * @return string
	 */
	public function buildTemporaryPath($absolute = FALSE, $mkdir = TRUE) {
		$path = '_temp_/'. $this->storagePath;
		$fileadminPath = PATH_site.'fileadmin/'.$path;
		$absPath = GeneralUtility::getFileAbsFileName($fileadminPath);
		if($mkdir) {
			if(!is_dir($absPath)) {
				GeneralUtility::mkdir_deep($fileadminPath);
			}
		}
		return $absolute ? $absPath : $path;
	}


	/**
	 * @param bool $absolute
	 * @param bool $mkdir
	 * @return string
	 */
	public function buildPermanentPath($absolute = FALSE, $mkdir = TRUE) {
		$path = $this->storagePath.date('Y').'/'.date('n').'/'.date('j');
		$fileadminPath = PATH_site.'fileadmin/'.$path;
		$absPath = GeneralUtility::getFileAbsFileName($fileadminPath);
		if($mkdir) {
			if(!is_dir($absPath)) {
				GeneralUtility::mkdir_deep($fileadminPath);
			}
		}
		return $absolute ? $absPath : $path;
	}


	/**
	 * @param $relFolderPath
	 * @param $pathExists
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	protected function createFolderObject($relFolderPath, $pathExists) {
		if(!$pathExists) {
			$storage = $this->folderRepository->findByUid(1);
			return $this->fileFactory->createFolderObject($storage, $relFolderPath, 'upload_folder');
		} else {
			return $this->fileFactory->getFolderObjectFromCombinedIdentifier("1:$relFolderPath");
		}
	}


	/**
	 * @return bool
	 */
	protected function temporaryPathExists() {
		$path = dir($this->buildTemporaryPath(TRUE, FALSE));
		return is_dir($path);
	}


	/**
	 * @return bool
	 */
	protected function permanentPathExists() {
		$path = dir($this->buildPermanentPath(TRUE, FALSE));
		return is_dir($path);
	}


	/**
	 * Returns the current plugin namespace
	 * @return string
	 */
	protected function getNamespace() {
		$framework = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$extension = $framework['extensionName'];
		$plugin = $framework['pluginName'];
		$namespace = 'tx_' . strtolower($extension) . '_' . strtolower($plugin);
		return $namespace;
	}


	/**
	 * @param string $propertyPath
	 * @return array
	 */
	protected function getUploadedFileData($propertyPath = '') {
		$fileData = array();
		$fd = $_FILES[$this->getNamespace()];

		$fileData['error'] = $this->valueByPath($fd['error'], $propertyPath);
		$fileData['type'] = $this->valueByPath($fd['type'], $propertyPath);
		$fileData['name'] = $this->valueByPath($fd['name'], $propertyPath);
		$fileData['size'] = $this->valueByPath($fd['size'], $propertyPath);
		$fileData['tmp_name'] = $this->valueByPath($fd['tmp_name'], $propertyPath);
		return $fileData;
	}


	/**
	 * Access array variables using the dot path notation
	 * i.e. document.image.file
	 *
	 * @param $subject
	 * @param string $path
	 * @return mixed
	 */
	protected function valueByPath($subject, $path = '') {
		$parts = explode('.', $path);
		return $this->_valueByPath($subject, $parts);
	}

	/**
	 * Recursive grunt for valueByPath
	 *
	 * @param $subject
	 * @param array $parts
	 * @return mixed
	 */
	protected function _valueByPath($subject, $parts = array()) {
		if(count($parts) == 0) {
			return $subject;
		}
		return $this->_valueByPath($subject[$parts[0]], array_slice($parts, 1));
	}


	/**
	 * Using error code from $_FILES array, creates the appropriate error message
	 *
	 * @param $uploadedFileData
	 * @return null
	 */
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


	/**
	 * @param $uploadedFileData
	 * @param $allowedMimes
	 * @return null
	 * @throws \Exception
	 */
	protected function validateType($uploadedFileData, $allowedMimes) {
		$filePath = $uploadedFileData['tmp_name'];
		$pathInfo = pathinfo($uploadedFileData['name']);
		$extension = strtolower($pathInfo['extension']);
		$valid = FALSE;
		if (!is_array($allowedMimes)) {
			throw new \Exception("Can't validate file allowed mime types. Must be an array like array(ext => 'mime/type', ...).");
		}
		if (isset($allowedMimes[$extension])) {
			$parsedAllowedMimes = GeneralUtility::trimExplode(',', $allowedMimes[$extension]);
			if (function_exists('finfo_file')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $filePath);
				finfo_close($finfo);
				$valid = in_array($mime, $parsedAllowedMimes);
			} elseif (function_exists('mime_content_type')) {
				$mime = mime_content_type($uploadedFileData['tmp_name']);
				$valid = in_array($mime, $parsedAllowedMimes);
			} else {
				// At least the extension is permitted, but no real check is done.
				$valid = TRUE;
			}
		}

		if (!$valid) {
			$this->addError('Invalid mime type: '.$uploadedFileData['type'], 1336597086);
		}
	}


	/**
	 * @param $uploadedFileData
	 * @param $maxSize
	 * @return null
	 */
	protected function validateSize($uploadedFileData,$maxSize) {
		if($uploadedFileData['size'] > $maxSize) {
			$this->addError('Uploaded file size ('.$uploadedFileData['size'].') exceeds max allowed size', 1336597087);
		} else {
			return NULL;
		}
	}


	/**
	 * Creates a new error message and
	 * adds to to our ongoing list of errors
	 *
	 * @param $msg
	 * @param $key
	 */
	protected function addError($msg, $key) {
		$error = new \TYPO3\CMS\Extbase\Validation\Error($msg, $key);
		$this->messages->addError($error);
	}


	/**
	 * Grabs string values from the locallang.xml file.
	 *
	 * @static
	 * @param string $string The name of the key in the locallang.xml file.
	 * @return string The value of that key
	 */
	protected static function translate($string) {
		return htmlspecialchars(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_sjcert_domain_model_municipalityclaim.' . $string, 'sjcert'));
	}

}

?>