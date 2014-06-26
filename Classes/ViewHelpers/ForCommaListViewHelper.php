<?php

namespace CIC\Cicbase\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Cicbase".                    *
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

class ForCommaListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Chunks each, iterates through chunks of $each and renders child nodes; this is a modified version of the for viewhelper in fluid
	 *
	 * @param string $each The array or \TYPO3\CMS\Extbase\Persistence\ObjectStorage to iterated over
	 * @param string $as The name of the iteration variable
	 * @param string $key The name of the variable to store the current array key
	 * @param boolean $reverse If enabled, the iterator will start with the last element and proceed reversely
	 * @param string $iteration The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)
	 * @return string Rendered string
	 * @author Lucas Thurston <lucas@castironcoding.com>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render($each, $as, $key = '', $reverse = FALSE, $iteration = NULL) {

		if ($each === NULL) {
			return '';
		}

		$each = explode(',',$each);
		$output = '';

		if ($reverse === TRUE) {
				// array_reverse only supports arrays
			if (is_object($each)) {
				$each = iterator_to_array($each);
			}
			$each = array_reverse($each);
		}
		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($each)
		);
	
		$output = '';
		foreach ($each as $keyValue => $singleElement) {
			$this->templateVariableContainer->add($as, $singleElement);
			if ($key !== '') {
				$this->templateVariableContainer->add($key, $keyValue);
			}
			if ($iteration !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$this->templateVariableContainer->add($iteration, $iterationData);
				$iterationData['index'] ++;
				$iterationData['cycle'] ++;
			}
			$output .= $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			if ($key !== '') {
				$this->templateVariableContainer->remove($key);
			}
			if ($iteration !== NULL) {
				$this->templateVariableContainer->remove($iteration);
			}
		}
		return $output;
	}

}

?>
