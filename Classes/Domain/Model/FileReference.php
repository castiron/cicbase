<?php
namespace CIC\Cicbase\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Since the experimental ExtBase FileReference model was not persisting properly,
 * we're extending it here to absorb the properties of the original resource object
 * which can then be persisted like a normal domain element.
 *
 * When ExtBase supports frontend file uploads, we can stop using this class.
 */
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference implements \CIC\Cicbase\Persistence\LimboObjectInterface {

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 * @inject
	 */
	protected $fileRepository;

	/** @var int */
	protected $uidLocal;

	/** @var string */
	protected $tableLocal;

	/** @var string */
	protected $tablenames;

	/** @var string */
	protected $title;

	/** @var string */
	protected $description;



	/**
	 * @param \TYPO3\CMS\Core\Resource\FileReference $originalResource
	 */
	public function setOriginalResource(\TYPO3\CMS\Core\Resource\FileReference $originalResource) {
		$this->originalResource = $originalResource;

		# Absorb properties from original resource, because otherwise we'll get an empty sys_file_reference row.
		$originalResourceProperties = $originalResource->getReferenceProperties();
		foreach ($originalResourceProperties as $propertyName => $propertyValue) {
			$setter = 'set' . GeneralUtility::underscoredToUpperCamelCase($propertyName);
			if (method_exists($this, $setter)) {
				$this->$setter($propertyValue);
			}
		}
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
	 * @param int $uidLocal
	 */
	public function setUidLocal($uidLocal) {
		$this->uidLocal = $uidLocal;
	}

	/**
	 * @return int
	 */
	public function getUidLocal() {
		return $this->uidLocal;
	}

	/**
	 * @param string $tablenames
	 */
	public function setTablenames($tablenames) {
		$this->tablenames = $tablenames;
	}

	/**
	 * @return string
	 */
	public function getTablenames() {
		return $this->tablenames;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
}

?>