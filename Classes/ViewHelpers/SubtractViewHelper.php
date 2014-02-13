<?php
namespace CIC\Cicbase\ViewHelpers;

/**
 * Add Slashes viewhelper
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class SubtractViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param integer $left
	 * @param integer $right
	 * @return integer
	 */
	public function render($left, $right) {
		return $left - $right;
	}
}

?>