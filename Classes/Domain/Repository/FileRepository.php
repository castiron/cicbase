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

	protected $lastAddedFile = NULL;

	protected $baseStoragePath = 'fileadmin/cicbase/documents';

	protected function getBaseStoragePath() {
		return $this->baseStoragePath;
	}

	public function getLastAddedFile() {
		return $this->lastAddedFile;
	}

	public function add($fileObject) {
		Tx_Extbase_Utility_Debugger::var_dump('in here',__FILE__ . " " . __LINE___);
		$baseStoragePath = $this->getBaseStoragePath();
		if($fileObject->getIsSaved == false) {
			$pathInfo = pathinfo($fileObject->getOriginalFilename());
			$extension = $pathInfo['extension'];
			$now = time();
			$year = date('Y', $now);
			$month = date('n', $now);
			$day = date('j', $now);
			$relativeDestinationPath = sprintf("%s/%s/%s/%s", $baseStoragePath, $year, $month, $day);
			$filename = $now.'.'.$extension;
			$absoluteDestinationPath = t3lib_div::getFileAbsFileName($relativeDestinationPath);
			if (!file_exists($absoluteDestinationPath)) {
				try {
					t3lib_div::mkdir_deep($absoluteDestinationPath);
				} catch (Exception $e) {
					// This is a 'compile-time' error, not a run-time one.
					// Throwing an exception is appropriate.
					throw new Exception ('Cannot create directory for storing files: '. $absoluteDestinationPath);
				}
			}
			$source = $fileObject->getPath();
			$absoluteDestinationPathAndFilename = $absoluteDestinationPath. '/' .$filename;
			if(!t3lib_div::upload_copy_move($source, $absoluteDestinationPathAndFilename)) {
				return new Tx_Extbase_Error_Error('Unable to save file', 1336600870);
			} else {
				$fileObject->setFilename($filename);
				$fileObject->setPath($relativeDestinationPath);
				$fileObject->setIsSaved(true);
				$this->lastAddedFile = $fileObject;
				return parent::add($fileObject);
			}
		} else {
			throw new Exception ('Cannot add an existing file object to the fileRepository');
		}
	}

}




?>