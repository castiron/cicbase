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
 * Sample Dist file would look something like this:
 *
 * {
 *   "dist":"../../Public/Javascript",
 *   "include":[
 *     "another-file.coffee",
 *     "some-file.coffee",
 *     "app.coffee"
 *   ]
 * }
 *
 */

class IncludeJavascriptFromDistFileViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var object
	 */
	var $spec;

	public function initializeArguments() {
		$this->registerArgument('distFile', 'string', 'Path to JSON file; file should contain paths (relative to itself) of files to include (can use globs), in order, as well as .', TRUE);
	}

	protected function init() {
		$this->spec = $this->getSpec();
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function render() {
		$out = '';
		$this->init();

		$lines = array();
		foreach($this->getRelativeIncludeFilenames() as $file) {
			if (file_exists($file)) {
				$lines[] = "<script src=\"$file\"></script>";
			}
		}

		return implode('', $lines);
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 */
	protected function getSpec() {
		$filePath = $this->distFilePath();
		try {
			$spec = json_decode(file_get_contents($filePath));
		} catch(\Exception $e) {
			throw new \Exception("Could not read include file for js inclusion: $filePath");
		}
		return $spec;
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 */
	protected function sourcePath() {
		$pathInfo = pathinfo($this->distFilePath());
		return $pathInfo['dirname'];
	}

	/**
	 * @return string
	 */
	protected function targetPath() {
		return GeneralUtility::resolveBackPath($this->sourcePath() . '/' . $this->spec->dist);
	}

	/**
	 * @throws \Exception
	 */
	protected function distFilePath() {
		$filePath = GeneralUtility::getFileAbsFileName($this->arguments['distFile'], TRUE);
		if(!$filePath || !file_exists($filePath)) {
			throw new \Exception("Could not get dist file for js inclusion: $filePath");
		}
		return $filePath;
	}

	/**
	 * @return array
	 */
	protected function toInclude() {
		return $this->spec->include;
	}

	/**
	 * @param string $absPath
	 * @return string
	 */
	protected function getRelativeFromAbsolutePath($absPath) {
		$path = str_replace(PATH_site, '', $absPath);
		return $path ? $path : '';
	}

	/**
	 * @return array
	 */
	protected function getRelativeIncludeFilenames() {
		$out = array();
		$toInclude = $this->toInclude();
		foreach($toInclude as $path) {
			$expanded = $this->expandPathSpec($path);
			$out = array_merge($out, $expanded);
		}
		return array_unique($out);
	}

	/**
	 * @param $filename
	 * @return array
	 */
	protected function expandPathSpec($filename) {
		$out = array();
		$path = $this->sourcePath() . '/' . $filename;
//		DANG! this only does libc globbing
		$paths = glob($path);
		foreach($paths as $srcFile) {
			$out[] = $this->getRelativeFromAbsolutePath(
				$this->toJsFileName(
					$this->sourcePathToTargetPath($srcFile)
				)
			);
		}
		return $out;
	}

	/**
	 * @param $path
	 * @return string
	 */
	protected function toJsFileName($path) {
		$i = pathinfo($path);
		if(!$i['extension']) {
			$path .= '.js';
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
		return str_replace($this->sourcePath(), $this->targetPath(), $path);
	}
}
