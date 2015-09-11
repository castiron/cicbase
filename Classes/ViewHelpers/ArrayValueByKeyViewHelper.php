<?php

namespace CIC\Cicbase\ViewHelpers;

/**
 * Allows you to access an array item by key
 *
 * Class ArrayAccessorViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class ArrayValueByKeyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param array $array
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function render($array, $key, $default = null) {
		return $array[$key] ? $array[$key] : $default;
	}
}
