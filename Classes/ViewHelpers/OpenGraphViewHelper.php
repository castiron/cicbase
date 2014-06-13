<?php
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * Sets various Open Graph tags in <head> of page. NOTE: Doesn't work in non-cached context.
 * TODO: Make this work with non-cached extensions
 * TODO: Ensure duplicates are not added to page? This isn't really a great approach to OpenGraph tags, in that case.  Need a widget or something a little more powerful.
 *
 * @package Cicbase
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */

class Tx_Cicbase_ViewHelpers_OpenGraphViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const IDENTIFIER_TS_KEY = '_openGraphMerge';
	const DEFAULT_HEADER_DATA_KEY = '100';

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('title', 'string', 'Title');
		$this->registerArgument('url', 'string', 'Url');
		$this->registerArgument('type', 'string', 'Type');
		$this->registerArgument('image', 'string', 'Image');
		$this->registerArgument('audio', 'string', 'Audio');
		$this->registerArgument('description', 'string', 'Description');
		$this->registerArgument('determiner', 'string', 'Determiner');
		$this->registerArgument('locale', 'string', 'Locale');
		$this->registerArgument('siteName', 'string', 'Site Name');
		$this->registerArgument('video', 'string', 'Video');
		$this->registerArgument('merge', 'boolean', 'Try to merge these og items with any previously specified ones (that used this viewhelper)');
	}

	/**
	 *
	 */
	public function render() {
		$tags = array();
		foreach($this->arguments as $k => $v) {
			if($v) {
				$k = GeneralUtility::camelCaseToLowerCaseUnderscored($k);
				switch($k) {
					case 'image':
						if(!GeneralUtility::isValidUrl($v)) {
							$v = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $v;
							if (!GeneralUtility::isValidUrl($v)) {
								break;
							}
						}
						$tags[] = '<meta property="og:' . strtolower($k) . '"' . ' content="' . htmlspecialchars($v) . '" />';
						break;
					case 'merge':
						break;
					default:
						$tags[] = '<meta property="og:' . strtolower($k) . '"' . ' content="' . htmlspecialchars($v) . '" />';
						break;
				}
			}
		}
		if(count($tags)) {
			$headerDataKey = $this->getHeaderDataKey();
			$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey] = 'TEXT';
			$existingConf = $GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.' ];
			if(!$existingConf) {
				$existingConf = array();
			}
			$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.' ] = array_merge($existingConf, array(
				'value' => implode('', $tags),
			));
			if($this->arguments['merge']) {
				$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.'][Tx_Cicbase_ViewHelpers_OpenGraphViewHelper::IDENTIFIER_TS_KEY] = $headerDataKey;
			}
		}
	}

	/**
	 * Gets a header data key, either a brand new, unused one, or the key of the last 'merge' one
	 *
	 * @return string
	 */
	protected function getHeaderDataKey() {
		if($this->arguments['merge']) {
			$key = array_reduce($GLOBALS['TSFE']->pSetup['headerData.'], function ($res, $v) {
				if(is_array($v) && $v[Tx_Cicbase_ViewHelpers_OpenGraphViewHelper::IDENTIFIER_TS_KEY]) {
					return $v[Tx_Cicbase_ViewHelpers_OpenGraphViewHelper::IDENTIFIER_TS_KEY];
				}
				return $res;
			}, false);
		}
		if(!$key) {
			$key = $this->getSuperlativeHeaderDataKey();
		}
		return $key;
	}

	/**
	 * @return mixed
	 */
	protected function getSuperlativeHeaderDataKey() {
		$highestKey = array_reduce(array_keys($GLOBALS['TSFE']->pSetup['headerData.']), function ($res, $v) {
			return max($res, intval($v));
		});
		return $highestKey ? (string)($highestKey * 2) : Tx_Cicbase_ViewHelpers_OpenGraphViewHelper::DEFAULT_HEADER_DATA_KEY;
	}
}