<?php

namespace CIC\Cicbase\Service\Asset;
use CIC\Cicbase\Utility\Path;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class ManifestResolutionService
 * @package CIC\Cicbase\Service\Asset
 *
 * Helps out with getting translated file names from a gulp-rev manifest file
 */
class ManifestResolutionService {
	/**
	 * @var string $manifestFile Absolute path to the manifest file
	 */
	protected $manifestFile;

	/**
	 * @var array
	 */
	protected $manifest;

	/**
	 * @param array $args
	 */
	public function __construct($args) {
		$this->manifestFile = $args['manifestFile'];
		$this->filter = $args['filter'];
		$this->initManifest();
	}

	/**
	 * Parse the manifest file
	 *
	 * @throws Exception
	 */
	protected function initManifest() {
		if (!is_file($this->manifestFile)) {
			throw new Exception("Can't open manifest file at $this->manifestFile");
		}
		$this->manifest = json_decode(file_get_contents($this->manifestFile), true);
	}

	/**
	 * @param $file
	 */
	public function getVersionFilenameFor($file) {
		$out = $file;
		$f = Path::noDir($file);
		if($this->manifest[$f]) {
			$out = $this->manifest[$f];
		}
		return $out;
	}

	/**
	 * @return array
	 */
	public function getAllFromManifest() {
		return array_unique(array_values($this->manifest));
	}

	/**
	 * @param $filter
	 * @return array
	 */
	public function getAllFromManifestByFilter($filter) {
		return $this->filterByKeys($this->manifest, $filter);
	}

	/**
	 * @param array $arr
	 * @param $filter
	 * @return array
	 */
	protected function filterByKeys($arr, $filter) {
		if(!$filter) {
			return $arr;
		}
		$out = array();
		foreach($arr as $k => $v) {
			if (preg_match($filter, $k)) {
				$out[$k] = $v;
			}
		}
		return $out;
	}
}
