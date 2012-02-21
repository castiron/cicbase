<?php

/**
	* Length viewhelper
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/
class Tx_Cicbase_ViewHelpers_LengthViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

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