<?php
namespace CIC\Cicbase\Domain\Model;
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

class File extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var integer
	 */
	protected $crdate;

	/**
	 * @var integer
	 */
	protected $tstamp;

	/**
	 * @var string
	 */
	protected $originalFilename;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $mimeType;

	/**
	 * @var integer
	 */
	protected $size;

	/**
	 * @var string
	 */
	protected $rootDirectory;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var bool
	 */
	protected $isSaved = false;

	/**
	 * ID of the feuser who created this file
	 *
	 * @lazy
	 * @var integer
	 */
	protected $owner;

	/**
	 * The name of the AWS bucket containing the file (if AWS is used)
	 *
	 * @var string
	 */
	protected $awsbucket;

	/**
	 * Returns the filename
	 *
	 * @return string $filename
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * Sets the filename
	 *
	 * @param string $filename
	 * @return void
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}

	/**
	 * Returns the originalFilename
	 *
	 * @return string $originalFilename
	 */
	public function getOriginalFilename() {
		return $this->originalFilename;
	}

	/**
	 * Sets the originalFilename
	 *
	 * @param string $originalFilename
	 * @return void
	 */
	public function setOriginalFilename($originalFilename) {
		$this->originalFilename = $originalFilename;
	}

	/**
	 * Returns the path
	 *
	 * @return string $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Sets the path
	 *
	 * @param string $path
	 * @return void
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * Returns the mimeType
	 *
	 * @return string $mimeType
	 */
	public function getMimeType() {
		return $this->mimeType;
	}

	/**
	 * Sets the mimeType
	 *
	 * @param string $mimeType
	 * @return void
	 */
	public function setMimeType($mimeType) {
		$this->mimeType = $mimeType;
	}

	/**
	 * Returns the size
	 *
	 * @return integer $size
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Sets the size
	 *
	 * @param integer $size
	 * @return void
	 */
	public function setSize($size) {
		$this->size = $size;
	}


	/**
	 * Returns the rootDirectory
	 *
	 * @return string $rootDirectory
	 */
	public function getRootDirectory() {
		return $this->rootDirectory;
	}

	/**
	 * Sets the rootDirectory
	 *
	 * @param string $rootDirectory
	 * @return void
	 */
	public function setRootDirectory($rootDirectory) {
		$this->rootDirectory = $rootDirectory;
	}

	/**
	 * @return string
	 */
	public function getAbsPathAndFileName() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->getPath().'/'.$this->getFilename());
	}

	public function getPathAndFileName() {
		if($this->getAwsbucket()) {
			return $this->getAwsPathAndFileName();
		} else {
			if($this->getPath()) {
				return $this->getPath() . '/' . $this->getFileName();
			} else {
				return $this->getFileName();
			}
		}
	}

	protected function getAwsPathAndFileName() {
		$bucket = $this->getAwsbucket();
		$domain = 's3.amazonaws.com';

		if($this->getPath()) {
			$pathAndFile = $this->getPath() . '/' . $this->getFileName();
		} else {
			$pathAndFile = $this->getFileName();
		}
		return 'http://'.$bucket.'.'.$domain.'/'.$pathAndFile;
	}


	/**
	 * @return bool
	 */
	public function checkIfFileExists() {
		if(file_exists($this->getAbsPathAndFileName())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param boolean $isSaved
	 */
	public function setIsSaved($isSaved) {
		$this->isSaved = $isSaved;
	}

	/**
	 * @return boolean
	 */
	public function getIsSaved() {
		return $this->isSaved;
	}

	/**
	 * @param integer $owner
	 */
	public function setOwner($owner) {
		$this->owner = $owner;
	}

	/**
	 * @return integer
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param string $awsbucket
	 */
	public function setAwsbucket($awsbucket) {
		$this->awsbucket = $awsbucket;
	}

	/**
	 * @return string
	 */
	public function getAwsbucket() {
		return $this->awsbucket;
	}

	/**
	 * @param int $tstamp
	 */
	public function setTstamp($tstamp) {
		$this->tstamp = $tstamp;
	}

	/**
	 * @return int
	 */
	public function getTstampObj() {
		$out = new DateTime();
		$out->setTimestamp($this->tstamp);
		return $out;
	}

	/**
	 * @param int $crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * @return DateTime
	 */
	public function getCrdateObj() {
		$out = new DateTime();
		$out->setTimestamp($this->crdate);
		return $out;
	}

	/**
	 * @return mixed
	 */
	public function getExtension() {
		$parts = explode('.',$this->getFilename());
		return $parts[1];
	}

	/**
	 * @return string
	 */
	public function getTypeKey() {
		// todo: make this more complete
		switch($this->getExtension()) {
			case 'avi':
			case 'mov':
			case 'flv':
			case 'mp4':
				$out = 'video';
			case 'pdf':
				$out = 'pdf';
				break;
			case 'jpg':
			case 'png':
			case 'jpeg':
			case 'gif':
				$out = 'image';
				break;
			case 'doc':
			case 'docx':
				$out = 'word';
			case 'xls':
			case 'xld':
			case 'xlsx':
			case 'xlsm':
				$out = 'excel';
				break;
		}
		return $out;
	}

}

?>