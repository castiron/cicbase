<?php

namespace CIC\Cicbase\Structures;


/**
 * A basic class that iterates through an array. This is
 * a starting point for structures that iterate through
 * a basic array, but apply some special features/constraints
 * to make the array more useful.
 *
 * Class AbstractArray
 * @package CIC\Cicbase\Structures
 */
class AbstractArray implements \Iterator, \ArrayAccess, \Countable {


	/** @var array */
	protected $storage;


	public function toArray() {
		return $this->storage;
	}

	public function current() {
		return current($this->storage);
	}

	public function next() {
		return next($this->storage);
	}

	public function key() {
		return key($this->storage);
	}

	public function valid() {
		return (bool) $this->current();
	}

	public function rewind() {
		return reset($this->storage);
	}

	public function offsetExists($offset) {
		return isset($this->storage[$offset]);
	}

	public function offsetGet($offset) {
		return $this->storage[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->storage[$value] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->storage[$offset]);
	}

	public function count() {
		return count($this->storage);
	}


}