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
		$query = $this->objects->getQuery();
		$query->setLimit($itemsPerPage);
		if ($this->currentPage > 1) {
			$query->setOffset((integer)($itemsPerPage * ($this->currentPage - 1)));
		}
		$modifiedObjects = $query->execute();

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
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
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
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage < $this->numberOfPages - 1){
			$pagination['nextNextPage'] = $this->currentPage + 2;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
            $pagination['previousPreviousPage'] = $this->currentPage - 2;
		}

		return $pagination;
	}
}

?>