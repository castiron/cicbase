<?php

/**
	* Strip linebreaks
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/

class Tx_Cicbase_ViewHelpers_UrlEncodeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
		* URL encodes children
		*
		* @return String
		* @author Gabe Blair <gabe@castironcoding.com>
	*/
	public function render() {
		return urlencode($this->renderChildren());
	}
}

?>