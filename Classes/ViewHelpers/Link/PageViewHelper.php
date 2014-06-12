<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Zach Davis <zach@castironcoding.com>, Cast Iron Coding
 *  Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding
 *  Gabe Blair <gabe@castironcoding.com>, Cast Iron Coding
 *  Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class Tx_Cicbase_ViewHelpers_Link_PageViewHelper
 *
 * Same as fluid's link.page VH, but adds a 'frontendPath' argument for generating
 * links to the frontend from a backend module (like a scheduled task).
 */
class Tx_Cicbase_ViewHelpers_Link_PageViewHelper extends Tx_Fluid_ViewHelpers_Link_PageViewHelper {

	/**
	 * @param integer $pageUid target page. See TypoLink destination
	 * @param array $additionalParams query parameters to be attached to the resulting URI
	 * @param integer $pageType type of the target page. See typolink.parameter
	 * @param boolean $noCache set this to disable caching for the target page. You should not need this.
	 * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
	 * @param string $section the anchor to be added to the URI
	 * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
	 * @param boolean $absolute If set, the URI of the rendered link is absolute
	 * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
	 * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = TRUE
	 *
	 * @param string $frontendPath Generates a frontend link (even if executed from backend module)
	 * @param string $backupDomain Use this domain, if a domain isn't found (like when this is called from scheduled task)
	 *
	 * @return string Rendered page URI
	 */
	public function render($pageUid = NULL, array $additionalParams = array(), $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $linkAccessRestrictedPages = FALSE, $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array(), $frontendPath = '', $backupDomain = '') {

		$uriBuilder = $this->controllerContext->getUriBuilder();
		$uri = $uriBuilder
			->reset()
			->setTargetPageUid($pageUid)
			->setTargetPageType($pageType)
			->setNoCache($noCache)
			->setUseCacheHash(!$noCacheHash)
			->setSection($section)
			->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
			->setArguments($additionalParams)
			->setCreateAbsoluteUri($absolute)
			->setAddQueryString($addQueryString)
			->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
			->build();

		if($frontendPath) {
			$parts = parse_url($uri);
			if(!$parts['host'] && $backupDomain) {
				$parts['host'] = $backupDomain;
			}
			$parts['query'] = preg_replace('/M=[^&]+/', '', $parts['query']);
			$parts['path'] = $frontendPath;
			$uri = self::http_build_url($parts);
		}

		$this->tag->addAttribute('href', $uri);
		$this->tag->setContent($this->renderChildren());

		return $this->tag->render();
	}

	/**
	 * The standard function seems to be missing. So here's
	 * a poor substitute.
	 *
	 * @static
	 * @param array $parts
	 * @return string
	 */
	public static function http_build_url(array $parts){
		$slash = '';
		$qMark = '';
		$hash = '';
		if(!$parts['scheme']){
			$parts['scheme'] = 'http';
		}
		if(!$parts['host']) {
			$parts['host'] = $_ENV['SERVER_NAME'];
		}
		if(substr($parts['path'], 0, 1) != '/'){
			$slash = '/';
		}
		if($parts['query']){
			$qMark = '?';
		}
		if(isset($parts['fragment'])){
			$hash = '#';
		}
		return
			$parts['scheme'].
			'://'.
			$parts['host'].
			$slash.
			$parts['path'].
			$qMark.
			$parts['query'].
			$hash.
			$parts['fragment'];
	}
}