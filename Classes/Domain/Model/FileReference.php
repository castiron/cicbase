<?php

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

class Tx_Cicbase_Domain_Model_FileReference extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * @var integer
	 */
	protected $uidLocal;

	/**
	 * @var integer
	 */
	protected $uidForeign;

	/**
	 * @var string
	 */
	protected $tablenames;

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $tableLocal;


	/**
	 * @param string $fieldName
	 */
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
	}

	/**
	 * @return string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
	 * @param string $tableLocal
	 */
	public function setTableLocal($tableLocal) {
		$this->tableLocal = $tableLocal;
	}

	/**
	 * @return string
	 */
	public function getTableLocal() {
		return $this->tableLocal;
	}

	/**
	 * @param int $tableNames
	 */
	public function setTablenames($tableNames) {
		$this->tableNames = $tableNames;
	}

	/**
	 * @return int
	 */
	public function getTablenames() {
		return $this->tableNames;
	}

	/**
	 * @param int $uidForeign
	 */
	public function setUidForeign($uidForeign) {
		$this->uidForeign = $uidForeign;
	}

	/**
	 * @return int
	 */
	public function getUidForeign() {
		return $this->uidForeign;
	}

	/**
	 * @param string $uidLocal
	 */
	public function setUidLocal($uidLocal) {
		$this->uidLocal = $uidLocal;
	}

	/**
	 * @return string
	 */
	public function getUidLocal() {
		return $this->uidLocal;
	}


}

?>