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

class Tx_Cicbase_Service_FileService implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Cicbase_Domain_Repository_FileRepository
	 */
	protected $fileRepository;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var array
	 */
	protected $allowedMimesAndExtensions = array();

	/**
	 * @var int
	 */
	protected $maxSize;

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
	 * inject the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManager configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Returns the allowedMimesAndExtensions.
	 *
	 * The array will be in the form: 'extension' => 'mime/type'
	 *
	 * @return array $allowedMimesAndExtensions
	 */
	public function getAllowedMimesAndExtensions() {
		return $this->allowedMimesAndExtensions;
	}

	/**
	 * Sets the allowedMimesAndExtensions.
	 *
	 * The array should be in the form: 'extension' => 'mime/type'
	 *
	 * @param array $allowedMimesAndExtensions
	 * @return void
	 */
	public function setAllowedMimesAndExtensions(array $allowedMimesAndExtensions) {
		$this->allowedMimesAndExtensions = $allowedMimesAndExtensions;
	}


	/**
	 * Returns the maxSize
	 *
	 * @return int $maxSize
	 */
	public function getMaxSize() {
		return $this->maxSize;
	}

	/**
	 * Sets the maxSize
	 *
	 * @param int $maxSize
	 * @return void
	 */
	public function setMaxSize($maxSize) {
		$this->maxSize = $maxSize;
	}

	/**
	 * Generates a link to the file.
	 *
	 * @param Tx_Cicbase_Domain_Model_File $file
	 * @return string
	 */
	public function generateLink(Tx_Cicbase_Domain_Model_File $file) {
		// Get the domain name
		$uriBuilder = $this->objectManager->get('Tx_Extbase_MVC_Web_Routing_UriBuilder');
		$uri = $uriBuilder->setCreateAbsoluteUri(true)->build();
		$parsed_url = parse_url($uri);
		$domain = $parsed_url['scheme'].'://'.$parsed_url['host'];

		// Get the relative location of the file
		$root = $file->getRootDirectory();
		$path = $file->getPath();
		$i = strpos($path, $root);
		$relPath = substr($path, $i);


		$link = $domain . '/' . $relPath;

		return $link;
	}

	/**
	 * This function creates a File object.
	 *
	 *
	 * @param string $rootDirectory The directory to save the uploaded file.
	 * @param array $errors An array that will contain any errors if no file object is created.
	 * @param boolean $useDateSorting If true, files will be sorted into directories by date ( i.e. "root/2012/4/24/file3895023.pdf")
	 * @return array An array of Tx_Cicbase_Domain_Model_File objects.
	 */
	public function createFileObjectFromForm($rootDirectory, &$errors = array(), $useDateSorting = true) {

		// Get $_FILES variables.
		$pluginNamespace = $this->getNamespace();
		$post = $_FILES[$pluginNamespace];

		$files = array();
		$fd = $this->getFileData($post);
		$count = count($fd['error']);
		for($i = 0; $i < $count; ++$i) {
			$errors[$i] = array();
			$errors[$i]['messages'] = array();

			$error = $fd['error'][$i];
			$mime = $fd['mime'][$i];
			$original = $fd['original'][$i];
			$size = $fd['size'][$i];
			$source = $fd['source'][$i];


			// Check for upload errors.
			if($error) {
				$errors[$i]['errorCode'] = $error;
				switch($error) {
					case 1:
					case 2: $errors[$i]['messages'][] = self::translate('errorTooBig');
						break;
					case 3: $errors[$i]['messages'][] = self::translate('errorPartialFile');
						break;
					case 4: $errors[$i]['messages'][] = self::translate('errorNoFile');
						break;
					case 5:
					case 6:
					case 7: $errors[$i]['messages'][] = self::translate('errorBadConfig');
						break;
					default:
						$errors[$i]['messages'][] = self::translate('errorUnknown');
				}
				$files[$i] = null;
				continue;
			}

			// Get other variables.
			$leftovers = '';
			$ext = self::getExtension($original, $leftovers);
			$now = time();
			if($useDateSorting) {
				$year = date('Y', $now);
				$month = date('n', $now);
				$day = date('j', $now);
				$path = sprintf("%s/%s/%s/%s",$rootDirectory, $year, $month, $day);
			} else {
				$path = $rootDirectory;
			}
			$filename = $leftovers.$now.'.'.$ext;
			$dest = t3lib_div::getFileAbsFileName($path);

			// Validate mime and size.
			$forbidden = $wrong = false;
			if(!self::validMime($mime, $ext, $forbidden, $wrong)) {
				if($forbidden) {
					$errors[$i]['messages'][] = self::translate('errorForbiddenMime');
				}
				if($wrong) {
					$errors[$i]['messages'][] = self::translate('errorMimeExtensionBadMatch');
				}
				$files[$i] = null;
				continue;
			}
			if (!self::validSize($size)) {
				$errors[$i]['messages'][] = self::translate('errorTooBig');
				$files[$i] = null;
				continue;
			}

			// Save the file.
			if(!file_exists($dest)) {
				try {
					t3lib_div::mkdir_deep($dest);
				} catch (Exception $e) {
					// This is a 'compile-time' error, not a run-time one.
					// Throwing an exception is appropriate.
					throw new Exception ('Cannot create directory for storing files: '.$dest);
				}
			}
			$dest .= '/'.$filename;
			if(!t3lib_div::upload_copy_move($source, $dest)) {
				$errors[$i]['messages'][] = self::translate('errorNotSaved');
				$files[$i] = null;
				continue;
			}


			// Save data to error variable
			$errors[$i]['filename'] = $filename;
			$errors[$i]['originalFilename'] = $original;
			$errors[$i]['mimeType'] = $mime;
			$errors[$i]['size'] = $size;
			$errors[$i]['path'] = $dest;
			$errors[$i]['rootDirectory'] = $rootDirectory;

			$files[$i] = $this->createFileFromArray($errors[$i]);
		}

		return $files;
	}

	/**
	 * This function creates a file object from an array with the keys:
	 * 'filename', 'originalFilename', 'path', 'mimeType', 'size',
	 * 'title', and 'description'.
	 *
	 * @param array $form
	 * @return Tx_Cicbase_Domain_Model_File
	 */
	public function createFileFromArray(array $form) {
		$file = $this->objectManager->create('Tx_Cicbase_Domain_Model_File');
		$file->setFilename($form['filename']);
		$file->setOriginalFilename($form['originalFilename']);
		$file->setPath($form['path']);
		$file->setMimeType($form['mimeType']);
		$file->setSize($form['size']);
		$file->setRootDirectory($form['rootDirectory']);
		$file->setTitle($form['title']);
		$file->setDescription($form['description']);

		return $file;
	}

	/**
	 * Returns an array with these keys:
	 *
	 * 'error', 'mime', 'original', 'size', 'source'
	 *
	 * @static
	 * @param $post An array of File form data that needs to be parsed
	 * @return array
	 */
	protected static function getFileData(array $post) {
		$data = array();
		$data['error'] = self::getFirstDeepValues($post['error']);
		$data['mime'] = self::getFirstDeepValues($post['type']);
		$data['original'] = self::getFirstDeepValues($post['name']);
		$data['size'] = self::getFirstDeepValues($post['size']);
		$data['source'] = self::getFirstDeepValues($post['tmp_name']);
		return $data;
	}

	protected static function getFirstDeepValues(array $array) {
		while(is_array($array)) {
			$keys = array_keys($array);
			$vals = array_values($array);
			$key = $keys[0];
			$array = $array[$key];
		}
		return $vals;
	}


	/**
	 * // TODO: This may need to be adjusted in the future
	 *
	 * @return string The namespace of the file
	 */
	protected function getNamespace() {
		$framework = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$extension = $framework['extensionName'];
		$plugin = $framework['pluginName'];
		$namespace = 'tx_'.strtolower($extension).'_'.strtolower($plugin);
		return $namespace;
	}


	/**
	 * Move the given file to the given destination. This will not only change
	 * the path property of the file, but the filename will also be updated to
	 * match the time of modification.
	 *
	 * @static
	 * @param Tx_Cicbase_Domain_Model_File $file
	 * @param string $destFolder
	 * @return boolean
	 */
	public static function move(Tx_Cicbase_Domain_Model_File &$file, $destFolder) {
		$curPath = $file->getPath();
		$original = $file->getOriginalFilename();
		$ext = self::getExtension($original, $leftovers);
		$newFilename = $leftovers.time().'.'.$ext;
		$newPath = $destFolder.'/'.$newFilename;

		if(!t3lib_div::upload_copy_move($curPath, $newPath)) {
			$file->setFilename($newFilename);
			$file->setPath($newPath);
			return true;
		}
		return false;
	}

	/**
	 * @static
	 * @param string $mimeType
	 * @param string $extension
	 * @param bool $forbidden True if mime is forbidden.
	 * @param bool $wrong True if mime extension doesn't match mime type.
	 * @return bool
	 */
	protected function validMime($mimeType, $extension, &$forbidden = false, &$wrong = false) {
		$allowedMimes = $this->allowedMimesAndExtensions;
		if(!$allowedMimes) {
			return true;
		}
		if(!$ext =  array_search($mimeType, $allowedMimes)) {
			$forbidden = true;
			return false;
		}
		// There can be multiple extensions per mimeTypes, so we can't just compare
		// $ext to $extension to make sure they match.
		$keys = array_keys($allowedMimes, $mimeType);
		$properMime = array_search($extension, $keys);
		if(!$properMime) {
			$wrong = true;
			return false;
		}
		return true;
	}

	/**
	 * @static
	 * @param integer $size
	 * @return bool
	 */
	protected function validSize($size) {
		$max = $this->maxSize;
		if(!$max) {
			return true;
		}
		if ($size > $max) {
			return false;
		}
		return true;
	}

	/**
	 * Get the extension from a filename
	 *
	 * @static
	 * @param string $filename
	 * @param string $leftover
	 * @return null
	 */
	protected static function getExtension($filename, &$leftover = '') {
		$matches = array();
		preg_match('/(.*)\.(.*)$/', $filename, $matches);
		$leftover = $matches[1];
		return $matches[2] ? $matches[2] : null;
	}


	/**
	 * Grabs string values from the locallang.xml file.
	 *
	 * @static
	 * @param string $string The name of the key in the locallang.xml file.
	 * @return string The value of that key
	 */
	protected static function translate($string) {
		return htmlspecialchars(Tx_Extbase_Utility_Localization::translate('tx_cicbase_domain_model_file.'.$string, 'cicbase'));
	}
}

?>