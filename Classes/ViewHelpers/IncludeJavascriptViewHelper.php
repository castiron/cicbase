<?php
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

class Tx_Cicbase_ViewHelpers_IncludeJavascriptViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var t3lib_PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * Inject the pageRenderer
	 *
	 * @param t3lib_PageRenderer pageRenderer
	 * @return void
	 */
	public function injectPageRenderer(t3lib_PageRenderer $pageRenderer) {
		$this->pageRenderer = $pageRenderer;
	}

	public function initializeArguments() {
		$this->registerArgument('type', 'strong', 'Media argument - see PageRenderer documentation', FALSE, 'text/javascript');
		$this->registerArgument('compress', 'boolean', 'Compress argument - see PageRenderer documentation', FALSE, TRUE);
		$this->registerArgument('forceOnTop', 'boolean', 'ForceOnTop argument - see PageRenderer documentation', FALSE, FALSE);
		$this->registerArgument('allWrap', 'string', 'AllWrap argument - see PageRenderer documentation', FALSE, '');
		$this->registerArgument('excludeFromConcatenation', 'string', 'ExcludeFromConcatenation argument - see PageRenderer documentation', FALSE, FALSE);
	}

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $path The path and filename of the resource (relative to Public resource directory of the extension).
	 * @param string $extensionName Target extension name. If not set, the current extension name will be used
	 * @param boolean $absolute If set, an absolute URI is rendered
	 * @param string $file Same as path, present only for backwards compatibility.
	 */
	public function render($path = NULL, $extensionName = NULL, $absolute = FALSE, $file = NULL) {

		// early versions of this view helper used $file instead of $path. Leaving this in for backwards compatibility.
		if($file) {
			$uri = $file;
		} else {
			if ($extensionName === NULL) {
				$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
			}
			$uri = 'EXT:' . t3lib_div::camelCaseToLowerCaseUnderscored($extensionName) . '/Resources/Public/' . $path;
			$uri = t3lib_div::getFileAbsFileName($uri);
			$uri = substr($uri, strlen(PATH_site));
		}

		$this->pageRenderer->addJsFile(
			$uri,
  			$this->arguments['type'],
			$this->arguments['compress'],
			$this->arguments['forceOnTop'],
			$this->arguments['allWrap'],
			$this->arguments['excludeFromConcatenation']
		);
	}
}
