<?php

namespace CIC\Cicbase\Utility;

/**
 *
 * This holds some useful path utility functions. Please add more!
 *
 * @package CIC\Utility
 */
class Path {



	/**
	 * 1. Get the extension
	 * 2. Set the extension (if $newExt is there)
	 *
	 * @param string $path
	 * @param string $newExt
	 * @return string
	 */
	public static function ext($path, $newExt = '') {
		if ($newExt) {
			return self::noExt($path) . '.' . ltrim($newExt, '.');
		}
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * @param string $path
	 * @return string mixed
	 */
	public static function dir($path) {
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * @param string $path
	 * @return string mixed
	 */
	public static function noDir($path) {
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * @param string $path
	 * @return string mixed
	 */
	public static function noExt($path) {
		$info = pathinfo($path);
		$dir = Arr::safe($info, 'dirname', '');
		if ($dir) {
			if ($dir == '.') return $info['filename'];
			return $dir .'/'. $info['filename'];
		}
		return $info['filename'];
	}

	/**
	 * Path::make('some/dir','file','xml')
	 * Path::make(['some','dir'], 'file', 'xml')
	 * Path::make(['some','dir','file','xml'])
	 * Path::make(['some','dir','file'], 'xml')
	 * Path::make(['some','dir'], 'file.xml')
	 * => 'some/dir/file.xml'
	 *
	 */
	public static function make() {
		$args = func_get_args();
		if (!count($args)) return '';

		$parts = array();
		foreach ($args as $arg) {
			if (is_array($arg)) {
				$parts = array_merge($parts, $arg);
			} else {
				$parts[] = $arg;
			}
		}

		$fileAndExt = '';
		$ext = array_pop($parts);
		if (strpos($ext, '.') !== FALSE) {
			$fileAndExt = $ext;
		}
		if (!$fileAndExt) {
			$file = array_pop($parts);
			$fileAndExt = $file . '.' . $ext;
		}

		$parts[] = $fileAndExt;
		return implode('/', $parts);
	}

	/**
	 * Returns an array like [$commonPath, $path1Additional, $path2Additional]
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return array
	 */
	public static function diff($path1, $path2) {
		$p1 = explode('/', Path::ext($path1) ? Path::dir($path1) : $path1);
		$p2 = explode('/', Path::ext($path2) ? Path::dir($path2) : $path2);
		return array(
			implode('/', array_intersect_assoc($p1, $p2)),
			implode('/', array_diff_assoc($p1, $p2)),
			implode('/', array_diff_assoc($p2, $p1)),
		);
	}

}
