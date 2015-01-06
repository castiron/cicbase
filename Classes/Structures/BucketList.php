<?php

namespace CIC\Cicbase\Structures;


/**
 * Class PriorityList
 * @package CIC\Cicbase\Structures
 *
 * This class exists to store items in an arbitrary order defined
 * by the order of buckets. For example, let's say you define the bucket
 * order as: 3,1,5,2,4. Then items associated with bucket 3 will be listed first,
 * items associated with bucket 1 will be listed next, etc. Items in the buckets
 * are listed in the order that they were received.
 *
 */
class BucketList implements \Countable, \Iterator {

	/** @var array */
	protected $buckets = array();

	/** @var int  */
	protected $count = 0;

	/** @var int */
	protected $index = 0;

	/** @var \SplFixedArray */
	protected $flattenedList;

	/** @var \SplFixedArray */
	protected $indexToBucket;

	/** @var array Stores extra info per bucket */
	protected $bucketInfo = array();

	/**
	 * The bucket IDs can be anything that works as an array index in PHP.
	 *
	 * @param array $bucketOrder
	 * @param bool $associativeWithBucketInfo
	 */
	public function __construct(array $bucketOrder, $associativeWithBucketInfo = FALSE) {
		if ($associativeWithBucketInfo) {
			foreach ($bucketOrder as $bucketID => $bucketInfo) {
				$this->buckets[$bucketID] = array();
				$this->bucketInfo[$bucketID] = $bucketInfo;
			}
		} else {
			foreach ($bucketOrder as $bucketID) {
				$this->buckets[$bucketID] = array();
			}
		}
	}


	/**
	 * @param mixed $bucket
	 * @param mixed $item
	 * @throws \Exception
	 */
	public function insert($bucket, $item) {
		if (!isset($this->buckets[$bucket])) {
			throw new \Exception("That bucket has not been defined.");
		}
		$this->buckets[$bucket][] = $item;
		++$this->count;
	}

	public function setBucketInfo($bucket, $info) {
		$this->bucketInfo[$bucket] = $info;
	}


	public function currentBucket() {
		return $this->indexToBucket[$this->index];
	}

	public function currentBucketInfo() {
		return $this->bucketInfo[$this->currentBucket()];
	}

	public function bucketExists($bucket) {
		return isset($this->buckets[$bucket]);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->count;
	}
	public function current() {
		return $this->flattenedList[$this->index];
	}
	public function next() {
		++$this->index;
	}
	public function key() {
		return $this->index;
	}
	public function valid() {
		return $this->index < $this->count;
	}

	/**
	 * Standard rewind, flattens the buckets into a list (just once)
	 */
	public function rewind() {
		$this->index = 0;
		if ($this->count && empty($this->flattenedList)) {
			$this->flattenedList = new \SplFixedArray($this->count);
			$this->indexToBucket = new \SplFixedArray($this->count);
			$i = 0;
			foreach ($this->buckets as $bucketID => $bucketItems) {
				foreach ($bucketItems as $item) {
					$this->indexToBucket[$i] = $bucketID;
					$this->flattenedList[$i] = $item;
					++$i;
				}
			}
		}
	}
}