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

class ForChunkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
    /**
     * @var bool
     */
    protected $escapeOutput = false;

	/**
	 * @return void
	 */
	public function initializeArguments()
	{
		$this->registerArgument('each', 'array', 'The array or \TYPO3\CMS\Extbase\Persistence\ObjectStorage to iterated over', true);
		$this->registerArgument('as', 'string', 'The name of the iteration variable', true);
		$this->registerArgument('key', 'string', 'The name of the variable to store the current array key', false);
		$this->registerArgument('chunkSize', 'integer', 'The size of the chunks to break this into', false);
		$this->registerArgument('reverse', 'boolean', 'If enabled, the iterator will start with the last element and proceed reversely', false);
		$this->registerArgument('iteration', 'string', 'The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)', false);
	}


	/**
	 * Chunks each, iterates through chunks of $each and renders child nodes; this is a modified version of the for viewhelper in fluid
	 *
	 * @return string Rendered string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @api
	 */
	public function render() {
		extract($this->arguments);

		$output = '';
		if ($each === NULL) {
			return '';
		}
		if (is_object($each) && !$each instanceof \Traversable) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('ForViewHelper only supports arrays and objects implementing Traversable interface' , 1248728393);
		}

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

		if($chunkSize) {
			if(is_array($each)) {
				$each = array_chunk($each,$chunkSize);
			} else {
				$each = $this->chunkIterable($each,$chunkSize);
			}
		}

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

	/**
	 * Chunks an object storage
	 * @param array the unchunked object storage or other iterable
	 * @param int $chunkSize How many per chunk
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage chunked object storages
	 */
	protected function chunkIterable($mixed,$chunkSize) {
		$chunkedObjectStorage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		$workingChunk = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');

		$c = 0;
		$dangler = false;
		foreach($mixed as $item) {
			if($c >= $chunkSize) {
				$chunkedObjectStorage->attach($workingChunk);
				$workingChunk = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
				$c = 0;
				$dangler = false;
			}
			$workingChunk->attach($item);
			$c++;
			$dangler = true;
		}

		if($dangler) {
			$chunkedObjectStorage->attach($workingChunk);
		}
		return $chunkedObjectStorage;
	}
}

?>
