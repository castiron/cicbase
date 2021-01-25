<?php

namespace CIC\Cicbase\ViewHelpers;

use TYPO3\CMS\Core\Core\Environment;

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

class IncludeStylesheetViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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
		$this->registerArgument('file', 'string', 'Path to file', true);
		$this->registerArgument('rel', 'string', 'Rel argument - see PageRenderer documentation', false, 'stylesheet');
		$this->registerArgument('media', 'strong', 'Media argument - see PageRenderer documentation', false, 'all');
		$this->registerArgument('title', 'string', 'Title argument - see PageRenderer documentation', false, '');
		$this->registerArgument('compress', 'boolean', 'Compress argument - see PageRenderer documentation', false, true);
		$this->registerArgument('forceOnTop', 'boolean', 'ForceOnTop argument - see PageRenderer documentation', false, false);
		$this->registerArgument('allWrap', 'string', 'AllWrap argument - see PageRenderer documentation', false, '');
		$this->registerArgument('excludeFromConcatenation', 'string', 'ExcludeFromConcatenation argument - see PageRenderer documentation', false, false);
	}

	/**
	 * @param string $file
	 */
	public function render() {
		// TODO: Would be nice if the file could begin with EXT:Sumpsink/ and return a relative path
		$this->pageRenderer->addcssfile(
			$this->arguments['file'],
  			$this->arguments['rel'],
  			$this->arguments['media'],
  			$this->arguments['title'],
			$this->arguments['compress'],
			$this->arguments['forceOnTop'],
			$this->arguments['allWrap'],
			$this->arguments['excludeFromConcatenation']
		);
	}
}
