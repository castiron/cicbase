<?php

namespace CIC\Cicbase\Utility;

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
			$res = $where($val, $key);
			if ($res === -1) break;
			if ($res) {
				$results[$key] = $val;
				++$found;
				unset($array[$key]);
				if ($limit > 0 && $found == $limit) {
					break;
				}
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
		$new = array();
		foreach ($keys as $key) {
			$new[$key] = $array[$key];
		}
		return $new;
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


}