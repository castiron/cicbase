<?php
namespace CIC\Cicbase\ViewHelpers\Widget\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Cicbase".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PaginateController extends \TYPO3\CMS\Fluid\ViewHelpers\Widget\Controller\PaginateController {

	/**
	 * @param integer $currentPage
	 * @throws \Exception
	 * @return void
	 */
	public function indexAction($currentPage = 1) {
			// set current page
		$this->currentPage = (integer)$currentPage;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		} elseif ($this->currentPage > $this->numberOfPages) {
			$this->currentPage = $this->numberOfPages;
		}

			// modify query
		$itemsPerPage = (integer)$this->configuration['itemsPerPage'];
		$offset = $this->currentPage > 1 ? (integer)($itemsPerPage * ($this->currentPage - 1)) : 0;

		if (is_array($this->objects)) {

			$modifiedObjects = array_slice($this->objects, $offset, $itemsPerPage);

		} elseif ($this->objects instanceof \TYPO3\CMS\Extbase\Persistence\QueryResultInterface) {

			$query = $this->objects->getQuery();
			$statement = $query->getStatement();
			if ($statement) {
				$sql = $statement->getStatement();
				$sql .= " LIMIT $itemsPerPage";
				if ($offset) {
					$sql .= " OFFSET $offset";
				}
				$query->statement($sql);

				// Since the query object is using a statement instead of QOM,
				// we need to manually count the result objects. :( This is because
				// Typo3DbBackend only supports QOM, and won't utilize the existing
				// $query->statement value.
				//
				// @see Typo3DbBackend->getObjectCountByQuery()
				//
				$objectArray = $this->objects->toArray();
				$this->numberOfPages = ceil(count($objectArray) / (integer) $this->configuration['itemsPerPage']);
			} else {
				$query->setLimit($itemsPerPage);
				if ($offset) {
					$query->setOffset($offset);
				}
			}
			$modifiedObjects = $query->execute();
		} else {
			throw new \Exception("Can't paginate over objects that are not in an array or a QueryResult object.");
		}



		$pagination = $this->buildPagination();

		$contentArguments = array(
			$this->widgetConfiguration['as'] => $modifiedObjects
		);

		$this->view->assign('contentArguments', $contentArguments);

		foreach($this->widgetConfiguration['arguments'] as $name => $value) {
			$this->view->assign($name,$value);
		}
		$this->view->assign('configuration', $this->configuration);
		$this->view->assign('pagination', $pagination);
	}

	/**
	 * Returns an array with the keys:
	 * pages - An array of all pages with meta data about each page, not useful really
	 * current - The number of the current page
	 * numberOfPages - The total number of pages
	 * nextPage - The page number of the next page, only provided if it exists
	 * nextNextPage - The page number of the page after the next page, only provided if exists
	 * previousPage - The page number of the previous page, if it exists
	 * previousPreviousPage - The page number of the page before the previous page, if it exists
	 * lastPage - The page number of the last page, provided if it's not the same nextPage, nextNextPage, or currentPage
	 * firstPage - The page number of the first page, provided if it's not the same as previousPage, previousPreviousPage, or currentPage
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$pages = array();
		for ($i = 1; $i <= $this->numberOfPages; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => ($i === $this->currentPage));
		}
		$pagination = array(
			'pages' => $pages,
			'current' => $this->currentPage,
			'numberOfPages' => $this->numberOfPages,
		);

		// If there are more pages, then provide nextPage
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		// If there are more pages and beyond the next one
		if ($this->currentPage < $this->numberOfPages - 1){
			$pagination['nextNextPage'] = $this->currentPage + 2;
		}
		// If we're not on the first page
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		// If we're not on the first or second page
		if ($this->currentPage > 2) {
			$pagination['previousPreviousPage'] = $this->currentPage - 2;
		}
		// If we're not on the last page and the nextPage is not the last page
		if ($this->numberOfPages != $this->currentPage && $this->numberOfPages != $pagination['nextPage']) {
			$pagination['lastPage'] = $this->numberOfPages;
		}
		// If we're not on the first page and the previousPage is not the first page
		if ($this->currentPage != 1 && $pagination['previousPage'] != 1) {
			$pagination['firstPage'] = 1;
		}

		return $pagination;
	}
}

?>