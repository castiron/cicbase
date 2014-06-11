<?php

/**
 * Class CurrentUriViewHelper
 *
 * @package CIC\Cicbase\ViewHelpers
 */
class Tx_Cicbase_ViewHelpers_CurrentUriViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @return mixed
	 */
	public function render() {
		return t3lib_div::getIndpEnv('REQUEST_URI');
	}
}