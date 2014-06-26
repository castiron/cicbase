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

/**
 * Address Model
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class Address {
	
	/**
	 * address
	 * @var string
	 */
	protected $address;
	
	/**
	 * city
	 * @var string
	 */
	protected $city;

	/**
	 * state
	 * @var string
	 */
	protected $state;

	
	/**
	 * zip code
	 * @var string
	 */
	protected $zip;
	
	/**
	 * Setter for address
	 *
	 * @param string $address public address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * Getter for address
	 *
	 * @return string public address
	 */
	public function getAddress() {
		return $this->address;
	}
	
	/**
	 *  Returns the full address all on one line, primarily used for geocoding
	 *
	 * @return string public address on one line
	 */
	public function getFullAddressOneLine() {
		return implode(', ',explode(chr(10),$this->getFullAddress()));
	}
	
	/**
	 *  Returns the full address on multiple lines.
	 */
	public function getFullAddress() {
		$a = array();
		$b = array();
		if($this->getAddress()) $a[] = $this->getAddress();
		if($this->getCity()) $b[] = $this->getCity();
		if($this->getState()) $b[] = $this->getState();
		if($this->getZip()) $b[] = $this->getZip();
		$a[] = implode(', ',$b);
		$out = implode(chr(10),$a);
		return $out;
	}
	
	/**
	 * Setter for state
	 *
	 * @param string $state State 
	 * @return void
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Getter for state
	 *
	 * @return string State 
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * Setter for city
	 *
	 * @param string $city City 
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Getter for city
	 *
	 * @return string City 
	 */
	public function getCity() {
		return $this->city;
	}
	
	/**
	 * Setter for zip
	 *
	 * @param string $zip zip code
	 * @return void
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	/**
	 * Getter for zip
	 *
	 * @return string zip code
	 */
	public function getZip() {
		return $this->zip;
	}
	
	
	
}

?>