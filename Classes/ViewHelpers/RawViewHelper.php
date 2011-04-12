<?php

/**
	* Add Slashes viewhelper
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/
class Tx_Cicbase_ViewHelpers_RawViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
		* Returns a raw value
		*
		* @param string $value
		* @return String value, unescapted!
		* @author Lorem Ipsum <lorem@example.com> // All true
	*/
	public function render($value) {
		return $value;
	}
}

?>