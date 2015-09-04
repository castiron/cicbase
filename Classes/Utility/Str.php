<?php

namespace CIC\Cicbase\Utility;

/**
 *
 * This holds some useful string utility functions. Please add more!
 *
 * @package CIC\Utility
 */
class Str {

	private static $pluralNoChange = array('child', 'fish', 'deer');
	private static $pluralIrregular = array(
		'man' => 'men', 'woman' => 'women', 'child' => 'children', 'tooth' => 'teeth',
		'person' => 'people', 'mouse' => 'mice',
	);

	/**
	 * Turns a string from underscored to camel case
	 *
	 * @param string $str
	 * @return string
	 */
	public static function cCase($str) {
		return preg_replace_callback('/_([A-Za-z])/', function($matches) { return strtoupper($matches[1]); }, $str);
	}

	/**
	 * Turns a string from camel case to underscored
	 *
	 * @param string $str
	 * @return string
	 */
	public static function uCase($str) {
		$uCased = preg_replace_callback('/([A-Z])/', function($matches) { return '_'.strtolower($matches[1]); }, $str);
		return strpos($uCased, '_') === 0 ? substr($uCased, 1) : $uCased;
	}

	/**
	 * @param integer $count
	 * @param string $singleStr
	 * @param null|string $pluralStr
	 * @return null|string
	 */
	public static function pluralize($count, $singleStr, $pluralStr = NULL) {
		if ($count == 1) return $singleStr;
		if ($pluralStr !== NULL) return $pluralStr;

		if (in_array($singleStr, self::$pluralNoChange)) return $singleStr;
		if (isset(self::$pluralIrregular[$singleStr])) return self::$pluralIrregular[$singleStr];

		$lastLetter = substr($singleStr, -1);
		switch ($lastLetter) {
			case 'y': return substr($singleStr, 0, -1) . 'ies';
			case 'f': return substr($singleStr, 0, -1) . 'ves';
			case 'o': return $singleStr . 'es';
		}
		$lastLetters = substr($singleStr, -2);
		switch ($lastLetters) {
			case 'fe': return substr($singleStr, 0, -2) . 'ves';
			case 'us': return substr($singleStr, 0, -2) . 'i';
			case 'is': return substr($singleStr, 0, -2) . 'es';
			case 'on': return substr($singleStr, 0, -2) . 'a';
		}

		return $singleStr . 's';
	}

	/**
	 * Convert a string to a normalized path with underscores
	 * Not reversible to original string
	 *
	 * @param $str
	 * @return mixed
	 */
	public static function toWebUsablePath($str) {
		$str = trim($str, '-_');
		$out = preg_replace('~\s~', '_', trim($str));
		return preg_replace('~[^-_.\/a-zA-Z0-9]~', '', $out);
	}
}
