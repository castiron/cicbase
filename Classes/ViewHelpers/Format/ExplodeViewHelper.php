<?php

namespace CIC\Cicbase\ViewHelpers\Format;

class ExplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * @param string $string
	 * @param string $delimiter
	 * @return array
	 */
	public function render($string, $delimiter) {
		return explode($delimiter, $string);
	}
}
?>
