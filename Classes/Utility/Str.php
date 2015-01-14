<?php

namespace CIC\Cicbase\Utility;

/**
 *
 * This holds some useful string utility functions. Please add more!
 *
 * @package CIC\Utility
 */
class Str {


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

}