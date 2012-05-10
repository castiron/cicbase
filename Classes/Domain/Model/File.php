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

class Tx_Cicbase_Domain_Model_File extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * @var string
	 */
	protected $filename;

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
		return t3lib_div::getFileAbsFileName($this->getPath().'/'.$this->getFilename());
	}

	public function getPathAndFileName() {
		return $this->getPath().'/'.$this->getFileName();
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
}

?>