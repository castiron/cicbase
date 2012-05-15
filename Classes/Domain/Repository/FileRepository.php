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

	public function __construct() {
		$this->cicbaseConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicbase']);
		parent::__construct();
	}

	protected function getBaseStoragePath() {
		return $this->baseStoragePath;
	}

	protected function getHoldStoragePath() {
		return $this->holdStoragePath;
	}

	protected function getCache() {
		try {
			$cache = $GLOBALS['typo3CacheManager']->getCache('cicbase_cache');
		} catch (t3lib_cache_exception_NoSuchCache $e) {
			throw new Exception ('Unable to load the cicbase cache.');
		}
		return $cache;
	}

	public function clearHeld() {
		$cache = $this->getCache();
		$cacheKey = 'heldFile_'.$GLOBALS['TSFE']->fe_user->id;
		$cache->remove($cacheKey);
	}

	public function getHeld() {
		$cache = $this->getCache();
		$cacheKey = 'heldFile_'.$GLOBALS['TSFE']->fe_user->id;
		$serializedData = $cache->get($cacheKey);
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

	public function hold($fileObject) {
		$baseStoragePath = $this->getHoldStoragePath();
		if($fileObject->getIsSaved() == false) {
			$relativeDestinationPath = $this->getRelativeDestinationPath($fileObject, $baseStoragePath);
			$destinationFilename = $this->getDestinationFilename($fileObject);
			$results = $this->moveToDestination($relativeDestinationPath, $destinationFilename, $fileObject, false);
			if($results instanceof Tx_Extbase_Error_Error) {
				return $results;
			} else {
				$cache = $this->getCache();
				$cacheKey = 'heldFile_'.$GLOBALS['TSFE']->fe_user->id;
				$cache->set($cacheKey,serialize($fileObject),array('heldFile'),3600);
				return $fileObject;
			}
		} else {
			throw new Exception ('Cannot hold a file object that has already been saved.');
		}
	}

	protected function getDestinationFilename($fileObject) {
		$pathInfo = pathinfo($fileObject->getOriginalFilename());
		$extension = $pathInfo['extension'];
		$now = time();
		$destinationFilename = $now.'.'.$extension;
		return $destinationFilename;
	}

	protected function getRelativeDestinationPath($fileObject, $storagePath) {
		$pathInfo = pathinfo($fileObject->getOriginalFilename());
		$extension = $pathInfo['extension'];
		$now = time();
		$year = date('Y', $now);
		$month = date('n', $now);
		$day = date('j', $now);
		$relativeDestinationPath = sprintf("%s/%s/%s/%s", $storagePath, $year, $month, $day);
		return $relativeDestinationPath;
	}

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

	protected function moveToAWSDestination($relativeDestinationPath, $destinationFilename, $fileObject, $isFinalDestination) {

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
			if (!t3lib_div::upload_copy_move($source, $absoluteDestinationPathAndFilename)) {
				return new Tx_Extbase_Error_Error('Unable to save file', 1336600870);
			} else {
				$fileObject->setFilename($destinationFilename);
				$fileObject->setPath($relativeDestinationPath);
			}
		}

	}

	public function add($fileObject) {
		$baseStoragePath = $this->getBaseStoragePath();
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
			throw new Exception ('Cannot add an existing file object to the fileRepository');
		}
	}

}




?>