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
 * This helper allows you to include CSS files referenced by a JSON dist file.  For use in a 'javascript
 * include' partial or similar... Specify $distFile like 'dist-main.json' or similar.
 *
 * Sample Dist file would look something like this:
 *
 * {
 *   "css": {
 *     "dist":"../../Public/Dist/Stylesheets",
 *     "include":[
 *       "styles.css",
 *     ],
 *     "squashTo": "all.min.css"
 *   }
 * }
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */

/**
 * Class IncludeCssFromDistFileViewHelper
 * @package CIC\Cicbase\ViewHelpers\Asset
 */
class IncludeCssFromDistFileViewHelper extends AbstractIncludeFromDistFileViewHelper {

	/**
	 * @var string
	 */
	var $scope = 'css';

	/**
	 * @param string $file
	 */
	protected function addViaTsfe($file) {
		/**
		 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $fe
		 */
		$fe = $GLOBALS['TSFE'];
		$fe->getPageRenderer()->addCssFile($file);
	}

	/**
	 * @return array
	 */
	protected function getFilesFromManifest() {
		return $this->manifestResolutionService()->getAllFromManifestByFilter('~.*\.css$~');
	}

	/**
	 * @param $path
	 * @return string
	 */
	protected function toCssFileName($path) {
		$i = pathinfo($path);
		if(!$i['extension']) {
			$path .= '.css';
		} else {
			$path = str_replace('.scss', '.css', $path);
		}
		return $path;
	}

	/**
	 * @param $path string
	 * @return string
	 */
	protected function sourcePathToTargetPath($path) {
		$out = $path;
		if(!$this->isCssPath($path)) {
			$test = $this->commonSourcePath();
			$diff = Path::diff($test, $path);
			$out = $this->getTargetDir() . $this->toCssFileName(Path::noDir($path));
		}
		return $out;
	}

	/**
	 * @return null|string
	 */
	protected function commonSourcePath() {
		return $this->absolutizeFileName(Path::common($this->toInclude()));
	}

	/**
	 * @param $path
	 * @return bool
	 */
	protected function isCssPath($path) {
		return Path::ext($path) === 'css';
	}

	/**
	 * @return array
	 */
	protected function toRender() {
		return $this->spec->{$this->scope}->render;
	}

	/**
	 * @return array
	 */
	protected function toinclude() {
		return $this->toRender();
	}
}
