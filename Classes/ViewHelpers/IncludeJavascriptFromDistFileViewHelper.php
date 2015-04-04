<?php
namespace CIC\Cicbase\ViewHelpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Exception;
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
	 * @throws Exception
	 */
	public function render() {
		$this->init();

		$lines = array();
		$files = $this->getFiles();
		foreach($files as $file) {
			$f = $this->absolutizeFileName($file);
			if (file_exists($f)) {
				$lines[] = "<script src=\"$file\"></script>";
			}
		}

		return implode('', $lines);
	}

	/**
	 * @return array
	 */
	protected function getFiles() {
		return $this->useMinified() ? array($this->squashTo()) : $this->getRelativeIncludeFilenames();
	}

	/**
	 * @return string
	 */
	protected function squashTo() {
		return $this->getRelativeFromAbsolutePath($this->targetPath() . '/' . $this->spec->js->squashTo);
	}

	/**
	 * @return bool
	 */
	protected function useMinified() {
		return $this->spec->js->squashTo && $GLOBALS['TYPO3_CONF_VARS']['FE']['t3seedMinifiedAssets'];
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	protected function getSpec() {
		$filePath = $this->distFilePath();
		try {
			$spec = json_decode(file_get_contents($filePath));
		} catch(Exception $e) {
			throw new Exception("Could not read include file for js inclusion: $filePath");
		}
		return $spec;
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	protected function basePath() {
		$pathInfo = pathinfo($this->distFilePath());
		return $pathInfo['dirname'];
	}

	/**
	 * @return string
	 */
	protected function targetPath() {
		return GeneralUtility::resolveBackPath($this->basePath() . '/' . $this->spec->js->dist);
	}

	/**
	 * @throws Exception
	 */
	protected function distFilePath() {
		$filePath = $this->absolutizeFileName($this->arguments['distFile'], false);
		if(!$filePath || !file_exists($filePath)) {
			throw new Exception("Could not get dist file for js inclusion: $filePath");
		}
		return $filePath;
	}

	/**
	 * Copied from core GeneralUtility::getFileAbsFileName with minor mods (no restricting to paths inside
	 * TYPO3_ROOT and resolve '../' using `realpath()`)
	 *
	 * @param $filename
	 * @param bool $onlyRelative If $onlyRelative is set (which it is by default), then only return values relative to the current PATH_site is accepted.
	 * @return string
	 */
	protected function absolutizeFileName($filename, $onlyRelative = TRUE) {
		if ((string)$filename === '') {
			return '';
		}
		$relPathPrefix = PATH_site;

		// Extension
		if (strpos($filename, 'EXT:') === 0) {
			list($extKey, $local) = explode('/', substr($filename, 4), 2);
			$filename = '';
			if ((string)$extKey !== '' && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey) && (string)$local !== '') {
				$filename = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey) . $local;
			}
		} elseif (!GeneralUtility::isAbsPath($filename)) {
			// relative. Prepended with $relPathPrefix
			$filename = $relPathPrefix . $filename;
		} elseif ($onlyRelative && !GeneralUtility::isFirstPartOfStr($filename, $relPathPrefix)) {
			// absolute, but set to blank if not allowed
			$filename = '';
		}
		if ((string)$filename !== '') {
			// checks backpath.
			return GeneralUtility::resolveBackPath($filename);
		}
		return '';
	}

	/**
	 * @return array
	 */
	protected function toInclude() {
		return $this->spec->js->include;
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
			$out = array_merge($out, $this->expandPathSpec($path));
		}
		return array_unique($out);
	}

	/**
	 * @param $filename
	 * @return array
	 */
	protected function expandPathSpec($filename) {
		$out = array();
		$path = $this->basePath() . '/' . $filename;
//		DANG! this only does libc globbing, so you can only "*.coffee" and not "**/*.coffee
		$paths = glob($path);
		foreach($paths as $srcFile) {
			$out[] = $this->getRelativeFromAbsolutePath(
				$this->sourcePathToTargetPath($srcFile)
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
			list($common, $targetPath) = Path::diff($this->targetPath(), $path);
			$out = "$common/$targetPath/" . $this->toJsFileName(Path::noDir($path));
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
}
