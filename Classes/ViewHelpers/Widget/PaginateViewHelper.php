<?php
namespace CIC\Cicbase\ViewHelpers\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "CICBase".                      *
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


class PaginateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Widget\PaginateViewHelper {

	/**
	 * @var \CIC\Cicbase\ViewHelpers\Widget\Controller\PaginateController
	 */
	protected $controller;

	/**
	 * @param \CIC\Cicbase\ViewHelpers\Widget\Controller\PaginateController $controller
	 * @return void
	 */
	public function injectController(\CIC\Cicbase\ViewHelpers\Widget\Controller\PaginateController $controller) {
		$this->controller = $controller;
	}

	/**
	 * @param array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $objects
	 * @param string $as
	 * @param array $configuration
	 * @param array $arguments
	 * @return string
	 */
	public function render($objects, $as, array $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE, 'maximumNumberOfLinks' => 99), $arguments = array()) {
		return $this->initiateSubRequest();
	}
}

?>