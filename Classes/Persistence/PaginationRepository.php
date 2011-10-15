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
class Tx_Cicbase_Persistence_PaginationRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 *
	 * 
	 * 
	 */
	protected function setTotalCount($count) {
		$this->totalCount = $count;
	}

	/**
	 *
	 * 
	 * 
	 */
	public function getResultsPerPage() {
		return $this->resultsPerPage;
	}

	/**
	 *
	 * 
	 * 
	 */
	public function getPage() {
		return $this->page;
	}
	
	/**
	 *
	 * 
	 * 
	 */
	public function getLimitSkip() {
		return $this->limitSkip;
	}
	
	/**
	 *
	 * 
	 * 
	 */
	public function getTotalPages() {
		return $this->totalPages;
	}

	/**
	 *
	 * 
	 * 
	 */
	protected function setPaginationRequest($params) {
		$this->requestedPage = $params['page'];
		$this->requestedResultsPerPage = $params['resultsPerPage'];
	}

	/**
	 * It would be great to move this out into another class, along with other pagination functions
	 * @param integer $resultsPerPage
	 * @param integer $count
	 * @param integer $page
	 * @return void
	 */	
	protected function prepareInitialPaginationInfo() {
		if($this->requestedResultsPerPage) {
			$totalPages = ceil(intval($this->totalCount) / intval($this->requestedResultsPerPage));
		}
		$correctedPagePointer = t3lib_div::intInRange($this->requestedPage,1,$totalPages,1);
		$limitSkip = ($correctedPagePointer - 1) * $this->requestedResultsPerPage;
		if($limitSkip < 1) {
			$limitSkip = 0;
		}
		$this->page = $correctedPagePointer;
		$this->totalPages = $totalPages;
		$this->limitSkip = $limitSkip;
		$this->resultsPerPage = $this->requestedResultsPerPage;
	}
	
	/**
	 *
	 * @return void
	 */
	public function getPaginationInformation() {
		$out = new stdClass;
		$out->rangeStart = $this->resultsPerPage * ($this->page - 1) + 1;
		$out->rangeEnd = ($out->rangeStart + $this->resultsPerPage - 1) > $this->totalCount ? $this->totalCount : ($out->rangeStart + $this->resultsPerPage - 1);
		$out->resultsPerPage = $this->resultsPerPage;
		$out->page = $this->page;
		$out->totalCount = $this->totalCount;
		$out->totalPages = $this->totalPages;
		return $out;
	}

}
?>