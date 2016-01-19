<?php

namespace CIC\Cicbase\ViewHelpers;

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
 * TODO: Rewrite this whole thing, it's kind of a mess
 *
 * @package Cicbase
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */

class OpenGraphViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const MERGE_TS_KEY = '_openGraphMerge';
	const DEFAULT_HEADER_DATA_KEY = '100';
	const REGISTER_KEY = 'CicOpenGraph';
	const MAX_IMAGE_COUNT = 3;

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
		$tags = $this->generateTags();
		if(count($tags)) {
			if($this->arguments['merge']) {
				$tags = $this->mergeWithStashedTags($tags);
			}
			$this->updatePageHeaderData($tags);
		}
	}

	/**
	 * @param $tags array
	 */
	protected function updatePageHeaderData($tags) {
		$headerDataKey = $this->getHeaderDataKey();

		$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey] = 'TEXT';

		if($this->arguments['merge']) {
			$this->markHeaderDataKeyAsMergeable($headerDataKey);
			$tagSet = $this->getStashedMergeableTags();
		} else {
			$tagSet = $tags;
		}
		$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.' ] = array(
			'value' => implode('', $tagSet),
		);

		$this->stashHeaderDataKey($headerDataKey);
	}

	/**
	 * @param $headerDataKey
	 */
	protected function stashHeaderDataKey($headerDataKey) {
		$GLOBALS['TSFE']->register[self::REGISTER_KEY]['usedKeys'] = array_merge($this->getStashedHeaderDataKeys(), array(
			$headerDataKey => $headerDataKey,
		));
	}

	/**
	 * @return array
	 */
	protected function getStashedHeaderDataKeys() {
		return $GLOBALS['TSFE']->register[self::REGISTER_KEY]['usedKeys'] ? $GLOBALS['TSFE']->register[self::REGISTER_KEY]['usedKeys'] : array();
	}

	/**
	 * @param $headerDataKey
	 */
	protected function markHeaderDataKeyAsMergeable($headerDataKey) {
		$GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.'][self::MERGE_TS_KEY] = $headerDataKey;
	}

	/**
	 * @param $headerDataKey string
	 * @return array
	 */
	protected function getExistingHeaderDataConf($headerDataKey) {
		return $GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.' ] ? $GLOBALS['TSFE']->pSetup['headerData.'][$headerDataKey . '.' ] : array();
	}

	/**
	 * @return array
	 * TODO: Duck type this thing
	 */
	protected function generateTags() {
		$tags = array();
		foreach($this->arguments as $k => $v) {
			if($v) {
				$k = GeneralUtility::camelCaseToLowerCaseUnderscored($k);
				switch($k) {
					case 'merge':
						break;
					case 'image':
						if ($this->canAddImages()) {
							if(!GeneralUtility::isValidUrl($v)) {
								$v = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . ltrim($v, '/');
								if (!GeneralUtility::isValidUrl($v)) {
									break;
								}
							}
							$temp = '<meta property="og:' . strtolower($k) . '"' . ' content="' . htmlspecialchars($v) . '" />';
							if (!$this->alreadyUsingTag($temp)) {
								$this->increaseImageCount();
								$this->trackTag($temp);
								$tags[$k] = $temp;
							}
						}
						break;
					default:
						$temp = '<meta property="og:' . strtolower($k) . '"' . ' content="' . htmlspecialchars($v) . '" />';
						if (!$this->alreadyUsingTag($temp)) {
							$this->trackTag($temp);
							$tags[$k] = $temp;
						}
						break;
				}
			}
		}
		return $tags;
	}

	/**
	 *
	 */
	protected function increaseImageCount() {
		$n = $this->getImageCount();
		$this->setImageCount($n+1);
	}

	/**
	 * @param $tag
	 */
	protected function trackTag($tag) {
		$tracked = $GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes'] ? $GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes'] : array();
		$tracked[] = GeneralUtility::shortMD5($tag);
		$GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes'] = $tracked;
	}

	/**
	 * @param $tag
	 * @return bool
	 */
	protected function alreadyUsingTag($tag) {
		if (!$GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes']) {
			$GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes'] = array();
		}
		$out = array_search(\TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($tag), $GLOBALS['TSFE']->register[self::REGISTER_KEY]['tagHashes']) !== false;
		return $out;
	}

	/**
	 * @param $n
	 */
	protected function setImageCount($n) {
		$GLOBALS['TSFE']->register[self::REGISTER_KEY]['imageCount'] = $n;
	}

	/**
	 * @return int
	 */
	protected function getImageCount() {
		return $GLOBALS['TSFE']->register[self::REGISTER_KEY]['imageCount'] ? $GLOBALS['TSFE']->register[self::REGISTER_KEY]['imageCount'] : 0;
	}

	/**
	 *
	 */
	protected function canAddImages() {
		return $this->getImageCount() < self::MAX_IMAGE_COUNT;
	}

	/**
	 * @param $data array
	 * @return array
	 */
	protected function mergeWithStashedTags($data) {
		$out = $data;
		$existing = $this->getStashedMergeableTags();
		if(count($existing)) {
			foreach($existing as $k => $v) {
				if($data[$k]) {
					switch($this->getMergeStrategyForField($k)) {
						default:
							$out[$k] = $data[$k];
							break;
						case 'concat':
							if (!$this->alreadyUsingTag($data[$k])) {
								$out[$k] = $data[$k] . $v; // Prepend, because FB linter picks these up in reverse order
							}
							break;
					}
				} else {
					$out[$k] = $data[$k];
				}
			}
		}
		$this->stashMergeableTags($out);
		return $out;
	}

	/**
	 * STUB METHOD.  REWRITE AS NEEDED.
	 * @param $f
	 * @return string
	 */
	protected function getMergeStrategyForField($f) {
		return $f === 'image' ? 'concat' : '';
	}

	/**
	 * @param $data
	 */
	protected function stashMergeableTags($data) {
		$GLOBALS['TSFE']->register[self::REGISTER_KEY]['tags'] = array_merge($this->getStashedMergeableTags(),$data);
	}

	/**
	 *
	 */
	protected function getStashedMergeableTags() {
		return $GLOBALS['TSFE']->register[self::REGISTER_KEY]['tags'] ? $GLOBALS['TSFE']->register[self::REGISTER_KEY]['tags'] : array();
	}

	/**
	 * Gets a header data key, either a brand new, unused one, or the key of the last 'merge' one
	 *
	 * @return string
	 */
	protected function getHeaderDataKey() {
		if($this->arguments['merge']) {
			$key = array_reduce($GLOBALS['TSFE']->pSetup['headerData.'], function ($res, $v) {
				if(is_array($v) && $v[OpenGraphViewHelper::MERGE_TS_KEY]) {
					return $v[OpenGraphViewHelper::MERGE_TS_KEY];
				}
				return $res;
			}, false);
		}
		if(!$key) {
			$key = $this->generateHeaderDataKey();
		}
		return $key;
	}

	/**
	 *
	 */
	protected function useReverseOutputOrder() {
		return true;
	}

	/**
	 * Get a key lower than the lowest one used by this viewhelper.
	 * This will ensure that the og tags are output in the reverse of
	 * the order in which they were added.  Facebook appears to pick
	 * up the last og:image tag first, when rendering share media.
	 */
	protected function generateHeaderDataKey() {
		$key = $this->getSuperlativeHeaderDataKey();
		if($this->useReverseOutputOrder()) {
			$lowest = $this->findLowestKey();
			if($lowest) {
				$key = $lowest - 1;
			}
		}
		return $key;
	}

	/**
	 * @return int
	 */
	protected function findLowestKey() {
		$usedKeys = $this->getStashedHeaderDataKeys();
		sort($usedKeys, SORT_NUMERIC);
		return intval(array_shift(array_values($usedKeys)));
	}

	/**
	 * @return mixed
	 */
	protected function getSuperlativeHeaderDataKey() {
		$highestKey = array_reduce(array_keys($GLOBALS['TSFE']->pSetup['headerData.']), function ($res, $v) {
			return max($res, intval($v));
		});
		return $highestKey ? (string)($highestKey * 2) : self::DEFAULT_HEADER_DATA_KEY;
	}
}