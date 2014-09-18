<?php

namespace CIC\Cicbase\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * This holds some useful array utility functions. Please add more!
 *
 * @package CIC\Utility
 */
class Arr {

	/**
	 * Remove and return values from an array using a callable.
	 *
	 * - If $limit is greater than 0, then only that many results can be returned.
	 * - If $where return -1, then that breaks the loop and returns early.
	 * - If $where returns true or equivalent to true, then that value is taken from the
	 *   given $array and added to the result set.
	 *
	 * @param array $array
	 * @param callable $where
	 * @param int $limit
	 * @return array
	 */
	public static function spliceWhere(array &$array, callable $where, $limit = -1) {
		$results = array();
		$found = 0;
		foreach ($array as $key => $val) {
			if ($limit >= 0 && $found == $limit) {
				break;
			}
			$res = $where($val, $key);
			if ($res === -1) break;
			if ($res) {
				$results[$key] = $val;
				++$found;
				unset($array[$key]);
			}
		}
		return $results;
	}

	/**
	 * Shuffle an array preserving the key/value pairs.
	 *
	 * @param array $array
	 * @return array Returns a new array
	 */
	public static function shuffleAssoc(array $array) {
		$keys = array_keys($array);
		shuffle($keys);
		return self::filterByKeys($array, $keys);
	}


	/**
	 * Selects random key/value pairs from the given array and returns
	 * a new array with those pairs.
	 *
	 * @param array $array
	 * @param int $count
	 * @return array
	 */
	public static function selectRand(array $array, $count = 1) {
		$keys = array_rand($array, $count);
		if (!is_array($keys)) {
			return $array[$keys];
		}
		return array_intersect_key($array, array_flip($keys));
	}

	/**
	 * @param array $array
	 * @return int
	 */
	public static function sum(array $array) {
		return array_reduce($array, function ($c, $v) { return $c += $v; }, 0);
	}

	/**
	 * Returns the key with the highest value. If there are multiple keys
	 * with the highest value, then the last one is returned.
	 * 
	 * @param array $array
	 * @return null|mixed
	 */
	public static function maxKey(array $array) {
		if (empty($array)) return NULL;
		$max = max($array);
		$flipped = array_flip($array);
		return $flipped[$max];
	}

	/**
	 * Returns the key with the lowest value. If there are multiple keys
	 * with the lowest value, then the last one is returned.
	 * 
	 * @param array $array
	 * @return null|mixed
	 */
	public static function minKey(array $array) {
		if (empty($array)) return NULL;
		$min = min($array);
		$flipped = array_flip($array);
		return $flipped[$min];
	}

	/**
	 * Returns a new array with only the elements with the given keys.
	 * Associations are preserved.
	 *
	 * @param array $array
	 * @param array $keys
	 * @return array
	 */
	public static function filterByKeys(array $array, array $keys) {
		return array_intersect_key($array, array_fill_keys($keys, NULL));
	}

	/**
	 * Converts array keys from underscore to lower camel case.
	 *
	 * @param array $array
	 */
	public static function keysUnderscoreToLowerCamelCase(array &$array) {
		$keys = array_keys($array);
		array_walk($keys, function (&$x) { $x = GeneralUtility::underscoredToLowerCamelCase($x); });
		$array = array_combine($keys, array_values($array));
	}


	/**
	 * Returns TRUE if the $target has a truey value for the given $keys.
	 *
	 * NOTE: Values with 0 are considered FALSE here.
	 *
	 * @param array $target
	 * @param array $keys
	 * @return bool
	 */
	public static function hasValuesForKeys(array $target, array $keys) {
		$onlyBykeys = self::filterByKeys($target, $keys);
		$onlyByKeysCount = count($onlyBykeys);
		if ($onlyByKeysCount !== count($keys)) return FALSE;
		array_walk($onlyBykeys, function(&$k) {$k = trim($k); });
		return $onlyByKeysCount == count(array_filter($onlyBykeys));
	}
}