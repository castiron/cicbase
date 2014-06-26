<?php

namespace CIC\Cicbase\ViewHelpers;

/**
	* Strip linebreaks
	*
	* @package TYPO3
	* @subpackage Fluid
	* @version
	*/

class UrlEncodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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