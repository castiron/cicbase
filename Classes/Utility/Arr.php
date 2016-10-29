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
	 * Returns a new array with only the elements with the given keys.
	 * Associations are preserved.
	 *
	 * @param array $array
	 * @param array $keys
	 * @return array
	 */
	public static function removeByKeys(array $array, array $keys) {
		return array_diff_key($array, array_fill_keys($keys, NULL));
	}

	/**
	 * Checks if two arrays have the same values for the given set of keys
	 *
	 * @param array $a
	 * @param array $b
	 * @param array $keys
	 * @param bool $keysRequired If true, this fails if either array (a or b) does not contain a value for the provided keys
	 * @return bool
	 */
	public static function isSameByKeys(array $a, array $b, array $keys, $keysRequired = FALSE) {
		if ($keysRequired && !(self::hasValuesForKeys($a, $keys) && self::hasValuesForKeys($b, $keys))) return FALSE;
		return !count(array_diff_assoc(self::filterByKeys($a, $keys), self::filterByKeys($b, $keys)));
	}

	/**
	 * @param array $defaults
	 * @param array $overrides
	 * @param bool $strict If strict, then only the keys in $defaults will be merged from overrides
	 */
	public static function defaults(array &$defaults, array $overrides = array(), $strict = FALSE) {
		if (!count($overrides)) return;
		if ($strict) {
			$overrides = self::filterByKeys($overrides, array_keys($defaults));
		}
		$defaults = array_merge($defaults, $overrides);
	}

	/**
	 * Converts array keys from underscore to lower camel case.
	 *
	 * @param array $array
	 */
	public static function keysUnderscoreToLowerCamelCase(array &$array) {
		$keys = array_keys($array);
		array_walk($keys, function (&$x) { $x = Str::cCase($x); });
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
	 * Tells you how two arrays are different according to their keys.
	 * Returns an array of the original key/values but split like so:
	 *   'both'  -> Keys are the same in both arrays
	 *   'alpha' -> Keys are unique to the alpha array
	 *   'beta'  -> Keys are unique to the beta array
	 *
	 * @param array $alpha
	 * @param array $beta
	 * @return array
	 */
	public static function describeDifferencesByKeys(array $alpha, array $beta) {
		return array(
			'both' => array_intersect_key($alpha, $beta),
			'alpha' => array_diff_key($alpha, $beta),
			'beta' => array_diff_key($beta, $alpha),
		);
	}

	/**
	 * Tells you how two arrays are different.
	 * Returns an array of the original values but split like so:
	 *   'both'  -> Values are the same in both arrays
	 *   'alpha' -> Values are unique to the alpha array
	 *   'beta'  -> Values are unique to the beta array
	 *
	 * @param array $alpha
	 * @param array $beta
	 * @return array
	 */
	public static function describeDifferences(array $alpha, array $beta) {
		return array(
			'both' => array_intersect($alpha, $beta),
			'alpha' => array_diff($alpha, $beta),
			'beta' => array_diff($beta, $alpha),
		);
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
	 * @param array $array
	 * @param callable $func Returns a new key
	 */
	public static function walkKeysRecursive(array &$array, callable $func) {
		if (!is_callable($func)) return;
		$out = array();
		foreach ($array as $key => $val) {
			$newKey = $func($key);
			if (is_array($val)) {
				self::walkKeysRecursive($val, $func);
			}
			$out[$newKey] = $val;
		}
		$array = $out;
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
	 * Split the items into newspaper ordered columns.
	 * For example, if there are 11 items and $columnCount is 4,
	 * then the columns look like this:
	 *
	 * col 1: 1, 2, 3
	 * col 2: 4, 5, 6
	 * col 3: 7, 8, 9
	 * col 4: 10, 11
	 *
	 * See how this is different than using `array_chunk` directly?
	 * We're not specifying the number of elements per chunk, but rather
	 * the number of columns.
	 *
	 * @param array $items
	 * @param integer $columnCount
	 * @return array
	 */
	public static function columnify(array $items, $columnCount)
	{
		$colCounts = array_fill(0, $columnCount, 0);
		for($i = 0; $i < count($items); $i++) {
			$colCounts[$i % $columnCount]++;
		}

		$cols = [];
		$colI = 0;
		reset($items);
		while(current($items)) {
			if(count($cols[$colI]) >= $colCounts[$colI]) {
				$colI++;
			}

			$cols[$colI][] = current($items);
			next($items);
		}

		return $cols;
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
	 * @param array|\Traversable $storage
	 * @return bool
	 */
	public static function isTraversable($storage) {
		return is_array($storage) || $storage instanceof \Traversable;
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
	 * Converts a comma list to an array. No-op for arrays. Throws exception
	 * if not a string or an array.
	 *
	 * @param array|string $items
	 * @param string $msg
	 * @return array
	 * @throws \Exception
	 */
	public static function commaListToArray($items, $msg = "Expected comma list or array") {
		if (is_string($items)) {
			$items = array_map('trim', explode(',', $items));
		}
		if (!is_array($items)) {
			throw new \Exception($msg);
		}
		if (!count($items)) {
			return array();
		}
		return $items;
	}

    /**
     * @param $items
     * @param string $msg
     * @return array
     */
    public static function commaListToIntArray($items, $msg = 'Expected comma list or array') {
        return array_map(function ($el) {
            return intval($el);
        }, static::commaListToArray($items, $msg));
    }

	/**
	 * Access an array element by index regardless of whether that
	 * index has been defined.
	 *
	 * @param array|\ArrayAccess $array
	 * @param mixed $index If $index is array, then it's assumed we should dig recursively
	 * @param mixed $default
	 * @return mixed
	 */
	public static function safe($array, $index, $default = NULL) {
		if (!is_array($index)) {
			return isset($array[$index]) ? $array[$index] : $default;
		}

		// Peel a layer off the onion
		$key = array_shift($index);

		// Wherever we are, it's not good. so quit
		if (!isset($array[$key])) {
			return $default;
		}

		// If there's nowhere left to go, we're done.
		if (count($index) == 0) {
			return $array[$key];
		}

		// Dig deeper
		return self::safe($array[$key], $index, $default);
	}

	/**
	 * @param array $array
	 * @param string $path
	 * @param mixed $default
	 * @param string $pathDelimiter
	 * @return mixed
	 * @throws \Exception
	 */
	public static function safePath($array, $path, $default = NULL, $pathDelimiter = '.') {
		if (!is_string($path)) {
			throw new \Exception("Arr::safePath path must be a string");
		}
		$steps = explode($pathDelimiter, $path);
		return self::safe($array, $steps, $default);
	}

	/**
	 * Sometimes you want to add elements to the end of an array,
	 * but in nested arrays you don't know if the array you're
	 * adding to has been initialized yet. This helps.
	 *
	 * @param array $array
	 * @param mixed $index
	 * @param null|mixed $value
	 */
	public static function safeAppend(array &$array, $index, $value = NULL) {
		if (!is_array($array[$index])) {
			$array[$index] = array();
		}
		$array[$index][] = $value;
	}

	/**
	 * De-duplicate an array of arrays by comparing the values of a given key of the sub-arrays
	 *
	 * @param array $array
	 * @param string|int $key
	 * @param bool $ignoreEmpties Whether to ignore falsy values for $key in the comparison
	 * @return array
	 */
	public static function dedupeByKey($array, $key, $ignoreEmpties = true) {
		$out = array();
		$keyHolder = '_____||_____key';
		$smashed = array();
		$ignored = array();
		$i = 0;
		foreach ($array as $k => $item) {
			if ($ignoreEmpties && !$item[$key]) {
				$ignored[$k] = $item;
				$item[$key] = $i . '__|||_TOREPLACE';
				$i++;
			}

			/**
			 * Stash the key -- we'll need to restore it later
			 */
			$item[$keyHolder] = $k;

			/**
			 * Use the serialized array for a key, and the target field as a value
			 */
			$serial = serialize($item);
			if (!$smashed[$serial]) {
				$smashed[$serial] = $item[$key];
			}
		}

		/**
		 * Er, this is absurdly unreadable sorry! It just gets the arrays unserialized and put back into a container
		 * array (albeit with the wrong keys)
		 */
		$hydrated = array_map('unserialize', array_flip(array_unique($smashed)));

		/**
		 * Build the output array by constructing it from pieces of the original input array
		 */
		foreach ($hydrated as $item) {
			$j = $item[$keyHolder];
			$out[$j] = $array[$j];
		}

		return $out;
	}

	/**
	 * Takes an array of uids and limits to integer characters and ensures uniqueness
	 * @param $array
	 * @return array
	 */
	public static function uniquePositiveInts($array) {
		if(!is_array($array)) return array();

		$newArray = array();
		foreach($array as $item) {
			if(is_int($item) || is_string($item)) {
				$item = intval($item);
				if($item > 0) {
					$newArray[] = $item;
				}
			}
		}
		return array_unique($newArray);
	}
}
