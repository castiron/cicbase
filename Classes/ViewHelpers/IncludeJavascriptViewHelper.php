<?php
namespace CIC\Cicbase\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Zachary Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
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
 * This view helper is based on Claus Due's simliar view helper in the FED extension. Thanks, Claus!
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */

class IncludeJavascriptViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * Inject the pageRenderer
	 *
	 * @param \TYPO3\CMS\Core\Page\PageRenderer pageRenderer
	 * @return void
	 */
	public function injectPageRenderer(\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer) {
		$this->pageRenderer = $pageRenderer;
	}

	public function initializeArguments() {
		$this->registerArgument('type', 'strong', 'Media argument - see PageRenderer documentation', FALSE, 'text/javascript');
		$this->registerArgument('compress', 'boolean', 'Compress argument - see PageRenderer documentation', FALSE, TRUE);
		$this->registerArgument('forceOnTop', 'boolean', 'ForceOnTop argument - see PageRenderer documentation', FALSE, FALSE);
		$this->registerArgument('allWrap', 'string', 'AllWrap argument - see PageRenderer documentation', FALSE, '');
		$this->registerArgument('excludeFromConcatenation', 'string', 'ExcludeFromConcatenation argument - see PageRenderer documentation', FALSE, FALSE);
		$this->registerArgument('extensionName', 'string', 'Extension containing the javascript file', FALSE, FALSE);
		$this->registerArgument('where', 'string', 'Tells the page renderer where to insert the javascript, can be header, footer, or footerLibs', FALSE, 'footer');
		$this->registerArgument('key', 'string', 'Only relevant for footer libraries, in which case its the key that identifies them', FALSE, FALSE);
	}

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $file The relative path of the resource (relative to Public resource directory of the extension).
	 */
	public function render($file = NULL) {
		if(!$this->arguments['extensionName']) {
			$this->arguments['extensionName'] = $this->controllerContext->getRequest()->getControllerExtensionName();
		}

		$uri = 'EXT:' . GeneralUtility::camelCaseToLowerCaseUnderscored($this->arguments['extensionName']) . $this->getResourcesPathAndFilename($file);
		$uri = GeneralUtility::getFileAbsFileName($uri);
		$uri = substr($uri, strlen(PATH_site));
        /**
         * No URI -- maybe this is an external URL? In any case, bail.
         */
        if (!$uri) {
            $this->handleExternalUrl($file);
            return;
        }
		switch ($this->arguments['where']) {
			case 'footer':
				$this->pageRenderer->addJsFooterFile(
					$uri,
					$this->arguments['type'],
					$this->arguments['compress'],
					$this->arguments['forceOnTop'],
					$this->arguments['allWrap'],
					$this->arguments['excludeFromConcatenation']
				);
			break;

			case 'footerLibs':
				$this->pageRenderer->addJsFooterLibrary(
					$this->arguments['key'],
					$uri,
					$this->arguments['type'],
					$this->arguments['compress'],
					$this->arguments['forceOnTop'],
					$this->arguments['allWrap'],
					$this->arguments['excludeFromConcatenation']
				);
			break;

			default:
				$this->pageRenderer->addJsFile(
					$uri,
					$this->arguments['type'],
					$this->arguments['compress'],
					$this->arguments['forceOnTop'],
					$this->arguments['allWrap'],
					$this->arguments['excludeFromConcatenation']
				);
			break;
		}
	}

    /**
     * @param $url
     */
    protected function handleExternalUrl($url) {
        if (!GeneralUtility::isValidUrl($url)) {
            return;
        }
        $scriptTag = $this->urlToScriptTag($url);
        switch ($this->arguments['where']) {
            case 'footer':
            case 'footerLibs':
                $this->pageRenderer->addFooterData($scriptTag);
                break;

            default:
                $this->pageRenderer->addHeaderData($scriptTag);
                break;
        }
    }

    /**
     * @param $url
     * @return string
     */
    protected function urlToScriptTag($url) {
        /**
         * Bail if not super duper
         */
        if (!$url) {
            return '';
        }

        /**
         * Bail if not magic
         */
        if (!GeneralUtility::isValidUrl($url)) {
            return '';
        }

        return "<script type=\"text/javascript\" src=\"$url\"></script>";

    }

	protected function getResourcesPathAndFilename($file) {
		if(substr($file,0,1) == '/') {
			return $file;
		} else {
			return '/Resources/Public/'.$file;
		}
	}
}
