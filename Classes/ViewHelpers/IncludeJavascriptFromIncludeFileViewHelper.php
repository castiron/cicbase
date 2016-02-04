<?php
namespace CIC\Cicbase\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Exception;

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
 * This helper allows you to include <script> tags for files referenced by a plaintext list.  For use in a 'javascript include' partial or similar...
 * Specify $includeFile like 'EXT:myext/Resources/Public/Javascript/.include' or similar; In the .include file, you reference .js files to include, one per line,
 * relative to the .include file itself
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 * @deprecated Please use IncludeJavascriptFromDistFileViewHelper instead
 *
 */

class IncludeJavascriptFromIncludeFileViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	public function initializeArguments() {
		$this->registerArgument('includeFile', 'string', 'Path to plain text include file; file should contain paths (relative to itself) of files to include, in order.', TRUE);
	}

	/**
	 * @return string
	 * @throws \TYPO3\CMS\Core\Exception
	 */
	public function render() {
		$out = '';
		$filePath = GeneralUtility::getFileAbsFileName($this->arguments['includeFile'], TRUE);
		if(!$filePath || !file_exists($filePath)) {
			throw new \Exception('Could not get include file for js inclusion');
		}

		try {
			$fileContents = trim(file_get_contents($filePath));
		} catch(\Exception $e) {
			throw new \Exception('Could not read include file for js inclusion');
		}

		$lines = array();
		$pathInfo = pathinfo($filePath);
		$basePath = $this->getRelativeFromAbsolutePath($pathInfo['dirname']);
		foreach (GeneralUtility::trimExplode(chr(10), $fileContents) as $line) {
			$file = "$basePath/$line";
			if (is_file($file)) {
				$lines[] = "<script src=\"$file\"></script>";
			}
		}

		if(count($lines)) {
			$out = implode("\n", $lines);
		}
		return $out;
	}

	/**
	 * @param string $absPath
	 * @return string
	 */
	protected function getRelativeFromAbsolutePath($absPath) {
		$path = str_replace(PATH_site, '', $absPath);
		return $path ? $path : '';
	}
}
