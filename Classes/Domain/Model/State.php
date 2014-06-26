<?php
namespace CIC\Cicbase\Domain\Model;
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Zach Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
*  			Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding, Inc
*  			
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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

class State extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	* name
	* @var string
	*/
	protected $name; 

	/**
	* shortName
	* @var string
	*/
	protected $shortName; 


	/**
	* Setter for name
	* @param string $name The name of the state
	* @return void
	*/
	public function setName($name) {
		$this->name = $name;
	}

	/**
	* Getter for name
	* @return string
	*/
	public function getName() {
		return $this->name; 
	}

	/**
	* Setter for shortName
	* @param string $shortName The short name of the state
	* @return void
	*/
	public function setShortName($shortName) {
		$this->shortName = $shortName;
	}

	/**
	* Getter for shortName
	* @return string
	*/
	public function getShortName() {
		return $this->shortName; 
	}

	/**
	* active
	* @var int
	*/
	protected $active; 

	/**
	* Setter for active
	* @param int $active true if active
	* @return void
	*/
	public function setActive($active) {
		$this->active = $active;
	}

	/**
	* Getter for active
	* @return int
	*/
	public function getActive() {
		return $this->active; 
	}
	
	
	
}

?>