<?php
namespace CIC\Cicbase\ViewHelpers\Asset;
use CIC\Cicbase\Utility\Path;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Gabe Blair <gabe@castironcoding.com>, Cast Iron Coding, Inc
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
 * This helper allows you to include <script> tags for files referenced by a plaintext list.  For use in a 'javascript include' partial or similar...
 * Specify $includeFile like 'EXT:myext/Resources/Public/Javascript/.include' or similar; In the .include file, you reference .js files to include, one per line,
 * relative to the .include file itself
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 * Sample Dist file would look something like this:
 *
 * {
 *   "js": {
 *     "dist":"../../Public/Dist/Javascript",
 *     "include":[
 *       "another-file.coffee",
 *       "some-file.coffee",
 *       "app.coffee"
 *     ],
 *     "squashTo": "all.min.js"
 *   }
 * }
 *
 */

/**
 * Class IncludeJavascriptFromDistFileViewHelper
 * @package CIC\Cicbase\ViewHelpers\Asset
 */
class IncludeJavascriptFromDistFileViewHelper extends AbstractIncludeFromDistFileViewHelper {

	/**
	 * @var string
	 */
	var $scope = 'js';

	/**
	 * @param $file
	 * @return string
	 */
	protected function outputForFile($file) {
		return "<script src=\"$file\"></script>";
	}

	/**
	 * @param $path
	 * @return string
	 */
	protected function toJsFileName($path) {
		$i = pathinfo($path);
		if(!$i['extension']) {
			$path .= '.coffee';
		} else {
			$path = str_replace('.coffee', '.js', $path);
		}
		return $path;
	}

	/**
	 * @param $path string
	 * @return string
	 */
	protected function sourcePathToTargetPath($path) {
		$out = $path;
		if(!$this->isJsPath($path)) {
			$out = $this->getTargetDir() . $this->toJsFileName(Path::noDir($path));
		}
		return $out;
	}

	/**
	 * @param $path
	 * @return bool
	 */
	protected function isJsPath($path) {
		return Path::ext($path) == 'js';
	}

	/**
	 * @param string $file
	 */
	protected function addViaTsfe($file) {
		/**
		 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $fe
		 */
		$fe = $GLOBALS['TSFE'];
		$fe->getPageRenderer()->addJsFooterFile($file);
	}
}
