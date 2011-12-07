<?php
/***************************************************************
*  Copyright notice
*
*  (c)  TODO - INSERT COPYRIGHT
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
 * Tx_Cicbase_Persistence_PaginationRepository
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Cicbase_Persistence_Repository extends Tx_Extbase_Persistence_Repository {

	/**
	 * Set the total count of records
	 * @param integer $count
	 * @return void
	 */
	protected function setTotalCount($count) {
		$this->totalCount = $count;
	}

	/**
	 * Get the results per page
	 * @return integer $resultsPerPage
	 */
	public function getResultsPerPage() {
		return $this->resultsPerPage;
	}

	/**
	 * Get the current page number
	 * @return integer $page
	 * 
	 */
	public function getPage() {
		return $this->page;
	}
	
	/**
	 * Get the number of records to skip based on results per page and total reocrds
	 * @return integer $limitSkip
	 * 
	 */
	public function getLimitSkip() {
		return $this->limitSkip;
	}
	
	/**
	 * Get the number of pages based on number of records and resultsPerPage
	 * @return integer totalPages
	 * 
	 */
	public function getTotalPages() {
		return $this->totalPages;
	}

	/**
	 * Set the number of results per page. This number should be validated from the outside. In other words, if only 20 or 0 are allowed, make sure it's one of those values
	 * 0 means all.
	 * 
	 */
	public function setResultsPerPage($resultsPerPage) {
		$this->resultsPerPage = (int) $resultsPerPage;
	}
	
	/**
	 * Set the number of requested page. Just needs to be a number.
	 * 
	 * 
	 */
	public function setRequestedPage($requestedPage) {
		$this->requestedPage = (int) $requestedPage;
	}

	/**
	 * It would be great to move this out into another class, along with other pagination functions
	 * @param integer $resultsPerPage
	 * @param integer $count
	 * @param integer $page
	 * @return void
	 */	
	protected function prepareInitialPaginationInfo() {
		if($this->resultsPerPage > 0) {
			$totalPages = ceil(intval($this->totalCount) / intval($this->resultsPerPage));
			$correctedPage = t3lib_div::intInRange($this->requestedPage,1,$totalPages,1);
			$limitSkip = t3lib_div::intInRange(($correctedPage - 1) * $this->resultsPerPage,0,$this->totalCount - 1);
		} else {
			$this->resultsPerPage = $this->totalCount;
			$totalPages = 1; // Viewing all
			$correctedPage = 1;
			$limitSkip = 0;
		}

		$this->page = $correctedPage;
		$this->totalPages = $totalPages;
		$this->limitSkip = $limitSkip;
	}
	
	/**
	 *
	 * @return void
	 */
	public function getPaginationInformation() {
		$out = new stdClass;
		$out->rangeStart = $this->totalCount ? $this->resultsPerPage * ($this->page - 1) + 1 : 0;
		$out->rangeEnd = ($out->rangeStart + $this->resultsPerPage - 1) > $this->totalCount ? $this->totalCount : ($out->rangeStart + $this->resultsPerPage - 1);
		$out->resultsPerPage = $this->resultsPerPage;
		$out->page = $this->page;
		$out->totalCount = $this->totalCount;
		$out->totalPages = $this->totalPages;
	
		return $out;
	}

	/**
	 *
	 * 
	 * 
	 */
	protected function setTotalCountFromParams($params) {
		$count = $this->countByParams($params);
		$this->setTotalCount($count);
	}

	/**
	 *
	 * 
	 * 
	 */
	public function countByParams($params) {
		unset($params['offset']);
		unset($params['limit']);
		return $this->getQueryByParams($params)->execute()->count();
	}

	/**
	 * @param mixed a query result or a collection, something that we can call ->getUid() on each item of
	 * @param string a comma list of uids
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	protected function orderResultsByUids($iterable,$uidArray) {
		$objectStorage = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');

		foreach($uidArray as $uid) {
			foreach($iterable as $item) {
				if($item->getUid() == $uid) {
					$objectStorage->attach($item);
				}
			}
		}
		return $objectStorage;
	}

}
?>