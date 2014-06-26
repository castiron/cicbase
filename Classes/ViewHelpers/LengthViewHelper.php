<?php

namespace CIC\Cicbase\ViewHelpers;

/**
	* Length viewhelper
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/
class LengthViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Returns the length of the subject
	 * @param string subject
	 * @return Integer
	 * @author Lorem Ipsum <lorem@example.com> // All true
	 */
	public function render($subject) {
		return strlen($subject);
	}
}

?>