<?php

namespace CIC\Cicbase\ViewHelpers;

/**
	* Add Slashes viewhelper
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/
class RawViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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