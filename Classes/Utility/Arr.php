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


	/**
	 * Returns the first value where the callable returns true.
	 *
	 * Callable takes $key, $val as args.
	 * Only works if no values are -1.
	 *
	 * @param array $array
	 * @param callable $where
	 * @return int|bool FALSE if $where is not callable, -1 if not found
	 */
	public static function find(array $array, callable $where) {
		if (!is_callable($where)) return FALSE;
		foreach ($array as $key => $val) {
			if ($where($key, $val)) {
				return $val;
			}
		}
		return -1;
	}


	/**
	 * Returns the first value where the callable returns true.
	 *
	 * Callable only takes $key as argument.
	 * Only works if no values are -1.
	 *
	 * @param array $array
	 * @param callable $where
	 * @return int|bool FALSE if $where is not callable, -1 if not found
	 */
	public static function findWithKey(array $array, callable $where) {
		if (!is_callable($where)) return FALSE;
		foreach ($array as $key => $val) {
			if ($where($key)) {
				return $val;
			}
		}
		return -1;
	}

	/**
	 * Creates a new array from a set of "rows" with the
	 * provided arguments extracted out:
	 *
	 * $rows = [
	 *   ['one' => 1, 'two' => 2, 'three' => 3],
	 *   ['one' => 'a', 'two' => 'b', 'three' => 'c']
	 * ];
	 *
	 * // You must pass a key or value field to use
	 * Arr::column($rows); // throws error, must provide a key or a value column to use
	 *
	 * // Passing value and key fields returns associative array
	 * Arr::column($rows, 'one', 'three') returns:  [3 => 1, 'c' => 'a']
	 *
	 * // Passing just value field returns indexed array
	 * Arr::column($rows, 'one')          returns:  [1, 'a']
	 *
	 * // Passing just key field returns same array of "rows" with the key field as the index
	 * Arr::column($rows, NULL, 'one')    returns:  [1 => ['one' => 1, ...], 'a' => [...]]
	 *
	 * @param array $rows
	 * @param mixed $valueColumn
	 * @param null $keyColumn
	 * @throws \Exception
	 * @return array
	 */
	public static function column(array $rows, $valueColumn = NULL, $keyColumn = NULL) {
		if ($valueColumn === NULL && $keyColumn === NULL) {
			throw new \Exception("You must pass a key or value column to use on the given rows.");
		}
		$newRows = array();

		if ($keyColumn !== NULL && $valueColumn !== NULL) {
			foreach ($rows as $row) $newRows[$row[$keyColumn]] = $row[$valueColumn];
			return $newRows;
		}

		if ($valueColumn) {
			foreach ($rows as $row) $newRows[] = $row[$valueColumn];
			return $newRows;
		}

		if ($keyColumn) {
			foreach ($rows as $row) $newRows[$row[$keyColumn]] = $row;
			return $newRows;
		}


	}


	/**
	 * Indexed if all keys are numeric (1 or "1")
	 *
	 * @param array $array
	 * @return bool
	 */
	public static function isIndexed(array $array) {
		foreach ($array as $key => $val) {
			if (!is_numeric($key)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Associative if no keys are numeric.
	 *
	 * @param array $array
	 * @return bool
	 */
	public static function isAssoc(array $array) {
		foreach ($array as $key => $val) {
			if (is_numeric($key)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Converts multidimensional associative arrays into a
	 * stdClass object. This works recursively, but whenever
	 * it comes across an indexed array (as opposed to
	 * associative) it will leave that as an array though
	 * it will convert any associative array values of that
	 * indexed array into stdClass objects.
	 *
	 * @param array $array
	 * @return \stdClass
	 */
	public static function toStdClass(array $array) {
		return self::recursiveToStdClass($array);
	}

	/**
	 * @param \stdClass $obj
	 * @return array
	 */
	public static function fromStdClass(\stdClass $obj) {
		return self::recursiveFromStdClass($obj);
	}

	/**
	 * @param mixed $val
	 * @return object
	 */
	protected static function recursiveToStdClass($val) {
		if (is_array($val)) {
			if (!Arr::isAssoc($val)) return $val;
			return (object) array_map(array('\CIC\Cicbase\Utility\Arr', 'recursiveToStdClass'), $val);
		} else {
			return $val;
		}
	}

	/**
	 * @param mixed $val
	 * @return array
	 */
	protected static function recursiveFromStdClass($val) {
		if (is_object($val)) {
			$val = get_object_vars($val);
		}
		if (is_array($val)) {
			return array_map(array('\CIC\Cicbase\Utility\Arr', 'recursiveFromStdClass'), $val);
		}

		return $val;
	}

	/**
	 * Access an array element by index regardless of whether that
	 * index has been defined.
	 *
	 * @param array|\ArrayAccess $array
	 * @param mixed $index
	 * @param null $default
	 * @return null
	 */
	public static function safe($array, $index, $default = NULL) {
		return isset($array[$index]) ? $array[$index] : $default;
	}
}