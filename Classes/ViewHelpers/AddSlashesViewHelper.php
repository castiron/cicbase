<?php

namespace CIC\Cicbase\ViewHelpers;

/**
	* Add Slashes viewhelper
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/
class AddSlashesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
		* Adds some slashes
		*
		* @param string $quoteType Can be "single" or "double"
		* @return String Duh
		* @author Lorem Ipsum <lorem@example.com> // All true
	*/
	public function render($quoteType) {
		$content =  $this->renderChildren();
		if($quoteType == 'single') {
			$content = str_replace('\'','\\\'',$content);
		} else {
			$content = str_replace('"','\"',$content);
		}
		return $content;
	}
}

?>