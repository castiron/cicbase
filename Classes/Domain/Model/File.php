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
}

?>