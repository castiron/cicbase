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
 * Sets a meta tag.  Will attempt to destroy other meta tags of the same name.
 * TODO: Make this work with non-cached extensions
 * TODO: Make this work with OpenGraph tags? Nah, that could be a different viewHelper, maybs.  But you could us some of what's here (killing off existing tags) to support that.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */

class MetaTagViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * These are hard-coded here, and refer to typoscript config keys in the COA that is plugin.tx_seobasics
	 * @var array
	 */
	protected $seoBasicsConfKeys = array(
		'title' => 20,
		'keywords' => 30,
		'description' => 40,
		'date' => 50,
	);

	/**
	 * @param string $content the "content" value of the meta tag
	 * @param string $name The name of the meta tag you want to set
	 */
	public function render($content, $name = 'description') {
		$name = strtolower($name);

		/**
		 * Check in headerData objects
		 */
		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
		$cObj->start($GLOBALS['TSFE']->page);

		if(is_array($GLOBALS['TSFE']->pSetup['headerData.'])) {
			foreach($GLOBALS['TSFE']->pSetup['headerData.'] as $k => $v) {
				if(is_array($v)) {
					$nameKey = substr($k, 0, strlen($k) - 1);
					$objType = $GLOBALS['TSFE']->pSetup['headerData.'][$nameKey];
					if($objType == 'TEXT' || $objType == 'COA') { // Let's just do text or COA for now
						$rendered = $cObj->cObjGetSingle($objType,$v);
						if (
							$this->looksLikeAMetaTag($rendered,$name)
						) {
							unset($GLOBALS['TSFE']->pSetup['headerData.'][$k]);
							unset($GLOBALS['TSFE']->pSetup['headerData.'][$nameKey]);
						}
					}
				}
			}
		}

		/**
		 * Look in additionalHeaderData
		 */
		if(is_array($GLOBALS['TSFE']->additionalHeaderData)) {
			foreach($GLOBALS['TSFE']->additionalHeaderData as $k => $val) {
				if($this->looksLikeAMetaTag($val,$name)) unset($GLOBALS['TSFE']->additionalHeaderData[$k]);
			}
		}

		/**
		 * Look in seo_basics config and kill it out if it's there
		 */
		if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('seo_basics') && $key = $this->seoBasicsConfKeys[$name]) {
			unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_seobasics.'][$key]);
			unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_seobasics.'][$key . '.']);
		}

		/**
		 * Add the new meta tag to the site, using the standard TYPO3 config mechanism for this
		 */
		$GLOBALS['TSFE']->pSetup['meta.'][$name] = htmlspecialchars($content);
	}

	/**
	 * @param $string
	 * @param $name
	 * @return bool
	 */
	protected function looksLikeAMetaTag($string,$name) {
		return preg_match('/<meta[^<]*name="' . $name . '"[^>]*>/i', $string)
			&& preg_match('/<meta[^<]*content="(.*)"[^>]*>/i', $string);
	}
}
?>