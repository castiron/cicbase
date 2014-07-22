<?php

/**
 * Class CurrentUriViewHelper
 *
 * @package CIC\Cicbase\ViewHelpers
 */
class Tx_Cicbase_ViewHelpers_CurrentUriViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @return mixed
	 */
	public function render() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');
	}
}