<?php
namespace CIC\Cicbase\ViewHelpers\Asset;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use CIC\Cicbase\Utility\Path;
use TYPO3\CMS\Core\Exception;

/**
 * Class AbstractIncludeFromDistFileViewHelper
 * @package CIC\Cicbase\ViewHelpers\Asset
 */
class AbstractIncludeFromDistFileViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var object
	 */
	var $spec;

	var $scope = 'none';

	public function initializeArguments() {
		$this->registerArgument('distFile', 'string', 'Path to JSON file; file should contain paths (relative to itself) of files to include (can use globs), in order, as well as .', TRUE);
		$this->registerArgument('where', 'string', 'Whether to render the includes "here", or insert them via "tsfe"', FALSE, 'tsfe');
	}

	protected function init() {
		$this->spec = $this->getSpec();
	}

	/**
	 * @return array
	 */
	protected function getFiles() {
		return $this->useMinified() ? $this->getMinifiedFilePaths() : $this->getRelativeIncludeFilenames();
	}

	/**
	 * @return string
	 */
	protected function squashTo() {
		return $this->targetPathRelative() . '/' . $this->spec->{$this->scope}->squashTo;
	}

	/**
	 * @return array
	 */
	protected function getMinifiedFilePaths() {
		$out = array();
		foreach ($this->getFilesFromManifest() as $file) {
			$out[] = $this->targetPathRelative() . '/' . $file;
		}
		return $out;
	}

	/**
	 * @return array
	 */
	protected function getFilesFromManifest() {
		return $this->manifestResolutionService()->getAllFromManifest();
	}

	/**
	 * @return bool
	 */
	protected function useMinified() {
		return $this->spec->{$this->scope}->squashTo && $GLOBALS['TYPO3_CONF_VARS']['FE']['t3seedMinifiedAssets'];
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
			throw new Exception("Could not read include file for inclusion: $filePath");
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
		return GeneralUtility::resolveBackPath($this->basePath() . '/' . $this->spec->{$this->scope}->dist);
	}

	/**
	 * @return string
	 */
	protected function targetPathRelative() {
		return $this->getRelativeFromAbsolutePath($this->targetPath());
	}

	/**
	 * @throws Exception
	 */
	protected function distFilePath() {
		$filePath = $this->absolutizeFileName($this->arguments['distFile'], false);
		if(!$filePath || !file_exists($filePath)) {
			throw new Exception("Could not get dist file for inclusion: $filePath");
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
		return $this->spec->{$this->scope}->include;
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
		// DANG! this only does libc globbing, so you can only "*.coffee" and not "**/*.coffee
		$paths = glob($path);
		foreach($paths as $srcFile) {
			$out[] = $this->getRelativeFromAbsolutePath(
				$this->sourcePathToTargetPath($srcFile)
			);
		}
		return $out;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function render() {
		$out = '';
		$this->init();
		$files = $this->getFiles();

		switch($this->arguments['where']) {
			case 'here':
				$out = $this->renderHere($files);
				break;
			case 'tsfe':
				$this->renderViaTsfe($files);
				break;
		}
		return $out;
	}

	/**
	 * @param array $files
	 * @return string
	 * @throws Exception
	 */
	protected function renderHere($files) {
		$lines = array();
		foreach($files as $file) {
			$f = $this->absolutizeFileName($file);
			if (file_exists($f)) {
				$lines[] = $this->outputForFile($file);
			}
		}

		return implode('', $lines);
	}

	/**
	 * @param array $files
	 */
	protected function renderViaTsfe($files) {
		foreach($files as $file) {
			/**
			 * TODO: get file name from manifest file
			 */
			$f = $this->absolutizeFileName($file);
			if (file_exists($f)) {
				$this->addViaTsfe($file);
			}
		}
	}

	/**
	 * @return \CIC\Cicbase\Service\Asset\ManifestResolutionService
	 */
	protected function manifestResolutionService() {
		return $this->objectManager->get('CIC\\Cicbase\\Service\\Asset\\ManifestResolutionService', array('manifestFile' => $this->targetPath() . '/' . $this->manifestFile()));
	}

	/**
	 * @return string
	 */
	protected function manifestFile() {
		return "$this->scope-manifest.json";
	}

	/**
	 * @param $file
	 */
	protected function getVersionedFileName($file) {

	}

	/**
	 * @param string $file
	 * @throws Exception
	 */
	protected function addViaTsfe($file) {
		throw new Exception('Class ' . get_class($this) . ' must implement ' . __FUNCTION__ . '()');
	}

	/**
	 * @return string
	 */
	protected function getTargetDir() {
		list($common, $targetPath) = Path::diff($this->targetPath(), $path);
		return "$common/$targetPath/";
	}

	/**
	 * @param $file
	 * @throws Exception
	 */
	protected function outputForFile($file) {
		throw new Exception('Class ' . get_class($this) . ' must implement ' . __FUNCTION__ . '()');
	}

	/**
	 * @param string $srcPath
	 * @return string
	 */
	protected function sourcePathToTargetPath($srcPath) {
		return $srcPath;
	}
}
