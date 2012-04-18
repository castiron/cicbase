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
	 * This function creates a File object.
	 *
	 *
	 * The $info array must have the following keys:
	 *   'fileNameInForm' - The name of the upload element in the form.
	 *   'pluginNamespace' - The name of the plugin namespace (i.e. if the generated form has this: name="Tx_MyPlugin[myName]", then the namespace is Tx_MyPlugin.
	 * 	 'argumentName' - The name of the argument passed into the generated html (i.e. if the generated form as this: name="Tx_MyPlugin[myFileObj][myName]", then the argument is called 'myFileObj'.)
	 *   'rootDirectory' - The path to store the files in.
	 *   'allowedMimesAndExtensions' - An array of permissible mime types and their extensions: 'extension' => 'mime/type'.
	 * 	 'maxFileSize' - Specifies the maximum file size.
	 *
	 * @param array $info An array containing the necessary info for getting the file from the form and validating.
	 * @param string $title
	 * @param string $description
	 * @param Tx_Cicbase_Domain_Model_File $file A possible pre-existing file that should be modified.
	 * @param array $errors An array that will contain any errors if no file object is created.
	 * @param boolean $useDateSorting If true, files will be sorted into directories by date ( i.e. "root/2012/4/24/file3895023.pdf")
	 * @return Tx_Cicbase_Domain_Model_File|null A null object is returned, if there were errors.
	 * // TODO: look up the plugin namespace dynamically.
	 */
	public function createFileObjectFromForm(array $info, $title = null, $description = null, Tx_Cicbase_Domain_Model_File &$file = null, &$errors = array(), $useDateSorting = true) {

		$errors['messages'] = array();

		// Get info variables.
		$pluginNamespace = $info['pluginNamespace'];
		$fileNameInForm = $info['fileNameInForm'];
		$argument = $info['argumentName'];
		$root = $info['rootDirectory'];
		$allowedMimes = $info['allowedMimesAndExtensions'];
		$maxSize = $info['maxFileSize'];

		// Get $_FILES variables.
		$post = $_FILES[$pluginNamespace];
		if(!$argument) {
			$error = $post['error'][$fileNameInForm];
			$mime = $post['type'][$fileNameInForm];
			$original = $post['name'][$fileNameInForm];
			$size = $post['size'][$fileNameInForm];
			$source = $post['tmp_name'][$fileNameInForm];
		} else {
			$error = $post['error'][$argument][$fileNameInForm];
			$mime = $post['type'][$argument][$fileNameInForm];
			$original = $post['name'][$argument][$fileNameInForm];
			$size = $post['size'][$argument][$fileNameInForm];
			$source = $post['tmp_name'][$argument][$fileNameInForm];
		}

		// Check for upload errors.
		if($error) {
			$errors['errorCode'] = $error;
			switch($error) {
				case 1:
				case 2: $errors['messages'][] = self::translate('errorTooBig');
					break;
				case 3: $errors['messages'][] = self::translate('errorPartialFile');
					break;
				case 4: $errors['messages'][] = self::translate('errorNoFile');
					break;
				case 5:
				case 6:
				case 7: $errors['messages'][] = self::translate('errorBadConfig');
					break;
				default:
					$errors['messages'][] = self::translate('errorUnknown');
			}
			return null;
		}

		// Get other variables.
		$ext = self::getExtension($original, $leftovers);
		$now = time();
		if($useDateSorting) {
			$year = date('Y', $now);
			$month = date('n', $now);
			$day = date('j', $now);
			$path = sprintf("%s/%s/%s/%s",$root, $year, $month, $day);
		} else {
			$path = $root;
		}
		$filename = $leftovers.$now.'.'.$ext;
		$dest = t3lib_div::getFileAbsFileName($path);

		// Validate mime and size.
		if(!self::validMime($mime, $ext, $allowedMimes, $errors) ||
			!self::validSize($size, $maxSize, $errors)) {
			return null;
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
			$errors['messages'][] = self::translate('errorNotSaved');
			return null;
		}


		// Save data to error variable
		$errors['filename'] = $filename;
		$errors['originalFilename'] = $original;
		$errors['mimeType'] = $mime;
		$errors['size'] = $size;
		$errors['path'] = $dest;

		// Create the file object.
		if(is_null($file))
			$file = $this->objectManager->create('Tx_Cicbase_Domain_Model_File');
		$file->setFilename($filename);
		$file->setMimeType($mime);
		$file->setOriginalFilename($original);
		$file->setPath($dest);
		$file->setSize($size);
		$file->setTitle($title);
		$file->setDescription($description);
		return $file;
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
	 * @param array $allowedMimes
	 * @param array $errors
	 * @return bool
	 */
	protected static function validMime($mimeType, $extension, array $allowedMimes, array &$errors) {
		if(!$ext =  array_search($mimeType, $allowedMimes)) {
			$errors['messages'][] = self::translate('errorForbiddenMime');
			return false;
		}
		if($ext != $extension) {
			$errors['messages'][] = self::translate('errorMimeExtensionBadMatch');
			return false;
		}
		return true;
	}


	/**
	 * @static
	 * @param integer $size
	 * @param integer $max
	 * @param array $errors
	 * @return bool
	 */
	protected static function validSize($size, $max, array &$errors) {
		if ($size > $max) {
			$errors['messages'][] = self::translate('errorTooBig');
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
		return Tx_Extbase_Utility_Localization::translate('tx_cicbase_domain_model_file.'.$string, 'cicbase');
	}
}

?>