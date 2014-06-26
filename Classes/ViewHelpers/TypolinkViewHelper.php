<?php

namespace CIC\Cicbase\ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 *	Wraps some of the basic Typolink settings in a viewhelper. Good for rendering links to pages and page titles
 *	when all you have is the page id.
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $parameter
	 * @param string $target
	 * @param int $noCache
	 * @param int $useCacheHash
	 * @param array $additionalParams
	 * @param string $ATagParams
	 * @param string $extTarget
	 * @return mixed
	 */
	public function render($parameter, $target='',$noCache=0,$useCacheHash=1,$additionalParams=array(),$ATagParams = '',$extTarget = '') {
		$typoLinkConf = array(
			'parameter' => $parameter,
		);

		if($target) {
			$typoLinkConf['target'] = $target;
		}

		if($target) {
			$typoLinkConf['extTarget'] = $extTarget;
		}

		if($noCache) {
			$typoLinkConf['no_cache'] = 1;
		}

		if($useCacheHash) {
			$typoLinkConf['useCacheHash'] = 1;
		}

		if(count($additionalParams)) {
			$typoLinkConf['additionalParams'] = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('',$additionalParams);
		}

		if(strlen($ATagParams)) {
			$typoLinkConf['ATagParams'] = $ATagParams;
		}

		$linkText = $this->renderChildren();

		$textContentConf = array(
			'typolink.' => $typoLinkConf,
			'value' => $linkText
		);

		return $GLOBALS['TSFE']->cObj->cObjGetSingle('TEXT',$textContentConf);
	}
}
?>