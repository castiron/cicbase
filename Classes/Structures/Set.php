<?php

namespace CIC\Cicbase\Structures;

/**
 * A basic Set implementation. Simply uses php array keys as hashes,
 * but guarantees uniqueness.
 *
 * NOTE: Right now, this is only for simple values (integers, strings, ...) that
 * can be listed as a key for an array. See array_unique for restrictions.
 *
 * Class Set
 * @package CIC\Cicbase\Structures
 */
class Set extends AbstractArray {


	public function __construct($vals = array()) {
		$vals = array_unique($vals);
		$this->storage = array_combine($vals, $vals);
	}

	/**
	 * @param array|Set $set
	 * @throws \Exception
	 */
	public function add($set) {
		if ($set instanceof Set) {
			$set = $set->toArray();
		} else if (!is_array($set)) {
			throw new \Exception("Can't union a set with anything but another set or an array");
		}

		$newVals = array_combine($set, $set);
		$this->storage = $this->storage + $newVals;
	}

	/**
	 * @param array|Set $set
	 * @throws \Exception
	 */
	public function remove($set) {
		throw new \Exception("not yet implemented");
	}

	/**
	 * @param array|Set $set
	 * @throws \Exception
	 * @return Set
	 */
	public function union($set) {
		throw new \Exception("not yet implemented");
	}

	/**
	 * @param array|Set $set
	 * @throws \Exception
	 * @return Set
	 */
	public function intersection($set) {
		throw new \Exception("not yet implemented");
	}


	public function toArray() {
		return array_values($this->storage);
	}

	public function offsetExists($offset) {
		throw new \BadMethodCallException("Sets do not have keys");
	}

	public function offsetGet($offset) {
		throw new \BadMethodCallException("Sets do not have keys");
	}

	public function offsetSet($offset, $value) {
		if ($offset) throw new \InvalidArgumentException("Sets do not have keys");
		$this->storage[$value] = $value;
	}

	public function offsetUnset($offset) {
		throw new \BadMethodCallException("Sets do not have keys");
	}

}