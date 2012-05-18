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

class Tx_Cicbase_ViewHelpers_IncludeJavascriptStringViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

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
		$this->registerArgument('name', 'string', 'Key for js snippet', FALSE, FALSE);
	}

	/**
	 *
	 */
	public function render() {
		$block = $this->renderChildren();
		$out = '<script type="text/javascript">'.$block.'</script>';
		# This doesn't work with user_ints
		#$this->pageRenderer->addJsFooterInlineCode($this->arguments['name'],$block,FALSE,FALSE);
		return $out;
	}
}
