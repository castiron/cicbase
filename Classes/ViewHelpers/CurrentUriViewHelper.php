<?php

namespace CIC\Cicbase\ViewHelpers;

/**
 * Class CurrentUriViewHelper
 *
 * @package CIC\Cicbase\ViewHelpers
 */
class CurrentUriViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @return mixed
	 */
	public function render() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');
	}
}