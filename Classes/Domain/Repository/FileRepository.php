<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Tx_Cicbase_Domain_Repository_FileRepository extends Tx_Extbase_Persistence_Repository {

	protected $baseStoragePath = 'fileadmin/cicbase/documents';
	protected $holdStoragePath = 'typo3temp/cicbase/documents';
	protected $AWSEnabled = true;
	protected $cicbaseConfiguration;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * inject the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Constructs a new Repository
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->cicbaseConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicbase']);
		parent::__construct($objectManager);
	}

	/**
	 * Returns base storage path
	 * @return string
	 */
	protected function getBaseStoragePath() {
		return $this->baseStoragePath;
	}

	/**
	 * Returns the path for held files
	 * @return string
	 */
	protected function getHoldStoragePath() {
		return $this->holdStoragePath;
	}

	protected function getCacheKey($key = '') {
		return 'heldFile_'.$GLOBALS['TSFE']->fe_user->id.$key;

	}

	/**
	 * Returns the cache object
	 * @return mixed
	 * @throws Exception
	 */
	protected function getCache() {
		try {
			$cache = $GLOBALS['typo3CacheManager']->getCache('cicbase_cache');
		} catch (t3lib_cache_exception_NoSuchCache $e) {
			throw new Exception ('Unable to load the cicbase cache.');
		}
		return $cache;
	}

	/**
	 * Clears the held file
	 * @param string $key
	 */
	public function clearHeld($key = '') {
		$cache = $this->getCache();
		$cache->remove($this->getCacheKey($key));
	}

	/**
	 * Returns the held file
	 * @return Tx_Cicbase_Domain_Model_File|null
	 */
	public function getHeld($key = '') {
		$cache = $this->getCache();
		$serializedData = $cache->get($this->getCacheKey($key));
		if($serializedData) {
			$fileObject = unserialize($serializedData);
			if(!$fileObject->getAwsbucket() && $fileObject->checkIfFileExists()) {
				return $fileObject;
			} elseif($fileObject->getAwsbucket()) {
				return $fileObject;
			} else {
				return NULL;
			}
		} else {
			return NULL;
		}
	}

	/**
	 * Holds an uploaded file
	 * @param Tx_Cicbase_Domain_Model_File $fileObject
	 * @param string $key
	 * @return Tx_Cicbase_Domain_Model_File|Tx_Extbase_Error_Error
	 * @throws Exception
	 */
	public function hold(Tx_Cicbase_Domain_Model_File $fileObject, $key = '') {
		$baseStoragePath = $this->getHoldStoragePath();
		if($fileObject->getIsSaved() == false) {
			$relativeDestinationPath = $this->getRelativeDestinationPath($fileObject, $baseStoragePath);
			$destinationFilename = $this->getDestinationFilename($fileObject);
			$results = $this->moveToDestination($relativeDestinationPath, $destinationFilename, $fileObject, false);
			if($results instanceof Tx_Extbase_Error_Error) {
				return $results;
			} else {
				$cache = $this->getCache();
				$cacheKey = $this->getCacheKey($key);
				$cache->set($cacheKey,serialize($fileObject),array('heldFile'),3600);
				return $fileObject;
			}
		} else {
			throw new Exception ('Cannot hold a file object that has already been saved.');
		}
	}

	/**
	 * Returns the destination file name
	 * @param Tx_Cicbase_Domain_Model_File $fileObject
	 * @return string
	 */
	protected function getDestinationFilename(Tx_Cicbase_Domain_Model_File $fileObject) {
		$pathInfo = pathinfo($fileObject->getOriginalFilename());
		$extension = $pathInfo['extension'];
		$now = microtime(true);
		$now = str_replace('.','', $now);
		$destinationFilename = $now.'.'.$extension;
		return $destinationFilename;
	}

	/**
	 * @param Tx_Cicbase_Domain_Model_File $fileObject
	 * @param $storagePath
	 * @return string
	 */
	protected function getRelativeDestinationPath(Tx_Cicbase_Domain_Model_File $fileObject, $storagePath) {
		$pathInfo = pathinfo($fileObject->getOriginalFilename());
		$extension = $pathInfo['extension'];
		$now = time();
		$year = date('Y', $now);
		$month = date('n', $now);
		$day = date('j', $now);
		$relativeDestinationPath = sprintf("%s/%s/%s/%s", $storagePath, $year, $month, $day);
		return $relativeDestinationPath;
	}

	/**
	 * @return AmazonS3
	 */
	protected function initializeS3() {
		$extensionPath = t3lib_extMgm::extPath('cicbase');
		require_once($extensionPath . 'Vendor/awssdk/sdk.class.php');
		CFCredentials::set(array(
			'production' => array(
				'key' => $this->cicbaseConfiguration['AWSKey'],
				'secret' => $this->cicbaseConfiguration['AWSSecret'],
				'default_cache_config' => '',
				'certificate_authority' => true
			),
			'@default' => 'production'
		));
		$s3 = new AmazonS3();
		return $s3;
	}

	/**
	 * @param $relativeDestinationPath
	 * @param $destinationFilename
	 * @param Tx_Cicbase_Domain_Model_File $fileObject
	 * @param boolean $isFinalDestination
	 * @return Tx_Extbase_Error_Error
	 * @throws Exception
	 */
	protected function moveToAWSDestination($relativeDestinationPath, $destinationFilename,Tx_Cicbase_Domain_Model_File $fileObject, $isFinalDestination) {

		// make sure we have adequate configuration.
		if(	!$this->cicbaseConfiguration['AWSTemporaryBucketName'] ||
			!$this->cicbaseConfiguration['AWSPermanentBucketName'] ||
			!$this->cicbaseConfiguration['AWSKey'] ||
			!$this->cicbaseConfiguration['AWSSecret']
		) {
			throw new Exception ('AWS File Storage is enabled, yet it is not properly configured in the extension manager');
		}

		// initialize the S3 object.
		$s3 = $this->initializeS3();

		// get the destination bucket.
		if($isFinalDestination) {
			$destinationBucket = $this->cicbaseConfiguration['AWSPermanentBucketName'];
		} else {
			$destinationBucket = $this->cicbaseConfiguration['AWSTemporaryBucketName'];
		}

		// source path
		$source = $fileObject->getPath();

		if($fileObject->getAwsbucket()) {
			// copy it to another bucket
			$sourceBucket = $fileObject->getAwsbucket();
			$sourceConfig = array('bucket' => $sourceBucket, 'filename' => $source. '/'. $fileObject->getFilename());
			$destinationConfig = array('bucket' => $destinationBucket, 'filename' => $relativeDestinationPath . '/' . $destinationFilename);
			$response = $s3->copy_object(
				$sourceConfig,
				$destinationConfig,
				array('acl' => AmazonS3::ACL_PUBLIC)
			);
			if($response->isOk()) {
				$deleteResponse = $s3->delete_object($sourceBucket, $source . '/' . $fileObject->getFilename());
			} else {
				return new Tx_Extbase_Error_Error('Unable to save file to AWS S3', 1336600878);
			}
		} else {
			// create a new object
			$response = $s3->create_object($destinationBucket, $relativeDestinationPath . '/' . $destinationFilename, array(
				'fileUpload' => $source,
				'contentType' => $fileObject->getMimeType()
			));
		}
		if($response->isOK()) {
			$fileObject->setFilename($destinationFilename);
			$fileObject->setPath($relativeDestinationPath);
			$fileObject->setAwsBucket($destinationBucket);
		} else {
			return new Tx_Extbase_Error_Error('Unable to save file to AWS S3', 1336600875);
		}
	}

	/**
	 * @param string $relativeDestinationPath
	 * @param string $destinationFilename
	 * @param $fileObject
	 * @param $isFinalDestination
	 * @return Tx_Extbase_Error_Error
	 * @throws Exception
	 */
	protected function moveToDestination($relativeDestinationPath, $destinationFilename, $fileObject, $isFinalDestination) {
		if($this->cicbaseConfiguration['enableAWS'] == true) {
			return $this->moveToAWSDestination($relativeDestinationPath, $destinationFilename, $fileObject, $isFinalDestination);
		} else {
			$absoluteDestinationPath = t3lib_div::getFileAbsFileName($relativeDestinationPath);
			if (!file_exists($absoluteDestinationPath)) {
				try {
					t3lib_div::mkdir_deep($absoluteDestinationPath);
				} catch (Exception $e) {
					// This is a 'compile-time' error, not a run-time one.
					// Throwing an exception is appropriate.
					throw new Exception ('Cannot create directory for storing files: ' . $absoluteDestinationPath);
				}
			}
			$source = $fileObject->getPath();
			$absoluteDestinationPathAndFilename = $absoluteDestinationPath . '/' . $destinationFilename;
			if(is_uploaded_file($source)) {
				if (!t3lib_div::upload_copy_move($source, $absoluteDestinationPathAndFilename)) {
					return new Tx_Extbase_Error_Error('Unable to save file', 1336600870);
				}
			} else {
				$source = $fileObject->getPath().'/'.$fileObject->getFilename();
				if(!rename($source, $absoluteDestinationPathAndFilename)) {
					return new Tx_Extbase_Error_Error('Unable to save file', 1336600870);
				}
			}
			$fileObject->setFilename($destinationFilename);
			$fileObject->setPath($relativeDestinationPath);
		}
	}

	/**
	 * @param  $fileObject
	 * @param string $key
	 * @return Tx_Extbase_Error_Error|void
	 * @throws Exception
	 */
	public function add($fileObject, $key = '') {
		$baseStoragePath = $this->getBaseStoragePath($key);
		if($fileObject->getIsSaved() == false) {
			$relativeDestinationPath = $this->getRelativeDestinationPath($fileObject, $baseStoragePath);
			$destinationFilename = $this->getDestinationFilename($fileObject);
			$results = $this->moveToDestination($relativeDestinationPath, $destinationFilename, $fileObject, true);
			if($results instanceof Tx_Extbase_Error_Error) {
				return $results;
			} else {
				$fileObject->setIsSaved(true);
				return parent::add($fileObject);
			}
		} else {
			return parent::add($fileObject);
		}
	}

	/**
 	 * Used to set object-specific storage pids, if desired.
	 */
	public function initializeObject() {
		$frameworkConfig = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$ext = $frameworkConfig['extensionName'];
		$plugin = $frameworkConfig['pluginName'];
		$configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $ext, $plugin);
		if ($configuration['storagePids'][$this->objectType]) {
			$this->internalPid = $configuration['storagePids'][$this->objectType];
			$this->defaultQuerySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
			$this->defaultQuerySettings->setStoragePageIds(explode(',', $configuration['storagePids'][$this->objectType]));
		}
	}

}




?>