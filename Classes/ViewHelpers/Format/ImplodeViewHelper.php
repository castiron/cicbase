<?php

namespace CIC\Cicbase\ViewHelpers\Format;

class ImplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * @param array $array
	 * @param string $separator
	 * @param string $prependToLast Use if you need to add something like 'and' before the last element in the list. Spaces added for you. Assumes oxford comma.
	 * @return string
	 */
	public function render($array, $separator = ', ', $prependToLast = '') {
		if ($prependToLast) {
			$len = count($array);
			$vals = array_values($array);

			switch ($len) {
				case 0: return '';
				case 1: return $vals[0];
				case 2: return $vals[0].' '.trim($prependToLast, ' ').' '.$vals[1];
				default:
					$i = $len - 1;
					$vals[$i] = trim($prependToLast, ' ') . ' ' . $vals[$i];
					return implode($separator, $vals);
			}
		}
		return implode($separator, $array);
	}
}
?>