<?php

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
 * A view helper for setting the document title in the <title> tag.
 *
 * = Examples =
 *
 * <page.title mode="prepend" glue=" - ">{blog.name}</page.title>
 *
 * <page.title mode="replace">Something here</page.title>
 *
 * <h1><page.title mode="append" glue=" | " display="render">Title</page.title></h1>
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */

class Tx_Cicbase_ViewHelpers_TitleViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {


	/**
	 * @param string $mode Method for adding the new title to the existing one.
	 * @param string $glue Glue the new title to the old title with this string.
	 * @param string $display If render, this tag displays it's children. By default it doesn't display anything.
	 * @return string Rendered content or blank depending on display mode.
	 * @author Nathan Lenz <nathan.lenz@organicvalley.coop>
	 */
	public function render($mode = 'replace', $glue = ' - ', $display = 'none') {
		$renderedContent = $this->renderChildren();

		$existingTitle = empty($GLOBALS['TSFE']->page['tx_seo_titletag']) ? $GLOBALS['TSFE']->page['title'] : $GLOBALS['TSFE']->page['tx_seo_titletag'];


		if ($mode === 'prepend' && !empty($existingTitle)) {
			$newTitle = $renderedContent.$glue.$existingTitle;
		} else if ($mode === 'append' && !empty($existingTitle)) {
			$newTitle = $existingTitle.$glue.$renderedContent;
		} else {
			$newTitle = $renderedContent;
		}

		$GLOBALS['TSFE']->page['title'] = $newTitle;
		$GLOBALS['TSFE']->indexedDocTitle = $newTitle;
		$GLOBALS['TSFE']->register['cicfluidTitle'] = $newTitle;
		if($GLOBALS['TSFE']->additionalHeaderData['title']) {

			$GLOBALS['TSFE']->additionalHeaderData['title'] = '<title>'.htmlspecialchars($newTitle).'</title>';
			$GLOBALS['TSFE']->additionalHeaderData['titleMetaTag'] = '<meta name="title" content="'. htmlspecialchars($newTitle).'">';

			if($GLOBALS['TSFE']->page['tx_seo_titletag']) {
				$GLOBALS['TSFE']->page['tx_seo_titletag'] = $newTitle;
			}
		}

		if ($display === 'render') {
			$out =  $renderedContent;
		} else {
			$out = '';
		}

		return $out;
	}
}
?>