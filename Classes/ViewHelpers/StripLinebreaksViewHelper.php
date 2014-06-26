<?php

namespace CIC\Cicbase\ViewHelpers;

/**
	* Strip linebreaks
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/

class StripLinebreaksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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