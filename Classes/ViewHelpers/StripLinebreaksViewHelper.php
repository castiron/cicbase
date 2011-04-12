<?php

/**
	* Strip linebreaks
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/

class Tx_Cicbase_ViewHelpers_StripLinebreaksViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
		* Removes linebreaks
		*
		* @return String Duh
		* @author Lorem Ipsum <lorem@example.com> // All true
	*/
	public function render() {
		return str_replace(array("\n","\r"),'',$this->renderChildren());
	}
}

?>