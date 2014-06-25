<?php


/******* THIS IS A BACKPORT FROM MASTER *****************************
 *
 *
 * Class HashStorage
 * @package CIC\Cicbase\Persistence\Storage
 *
 * With ExtBase's normal ObjectStorage you have direct access to extra
 * information about an object. However, when populating ExtBase models
 * it doesn't provide anything here. So, the direct access feature is
 * pretty useless.
 *
 * $category = // Some category
 * $categories = // Some storage
 * $extraInfo = $categories[$category]
 *
 * Now $extraInfo is the simply NULL, kinda dumb right?
 *
 *
 * HashStorage, however, allows you to specify a hash key for storing objects:
 *
 * $categories = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
 * // now fill $categories ...
 * $uid = // Some category UID
 * $foundCategory = $categories[$uid];
 *
 * Now, you have direct access to the object you're looking for based on a key.
 *
 *
 *
 *
 * There are several ways of setting the key.
 *
 * 1) If you're using ExtBase objects (most likely), you can just specify a property.
 * $storage = new HashStorage('uid');
 *
 * 2) You can also specify a getter:
 * $storage = new HashStorage(array('method' => 'getStandardizedTitle'));
 *
 * 3) Or if you want to get fancy, you can use a callback/closure
 * $storage = new HashStorage(function($obj) { return $obj->getUid() * -1 });
 *
 *
 *
 *
 * NOTE: When ExtBase thaws values from a database, you still need to use the
 * ObjectStorage class. This is because ExtBase doesn't take into account
 * that you may want to use a different storage class. So, to use this class
 * you have to handle this manually:
 *
 * /**
 *  * USE ORIGINAL OBJECT STORAGE CLASS!!!! ExtBase won't know it's an object storage property otherwise.
 *  * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
 *  *
 * protected $categories;
 *
 * // Override the setProperty method...
 * public function _setProperty($propertyName, $propertyValue) {
 *   if ($propertName == 'categories') {
 *     $this->categories = new HashMap('uid');
 *     $this->categories->addAll($propertyValue);
 *   } else {
 *     parent::_setProperty($propertyName, $propertyValue);
 *   }
 * }
 *
 * $categories = new HasStorage('uid');
 * $categories->addAll($this->categories);
 *
 */
class Tx_Cicbase_Persistence_Storage_HashStorage extends \Tx_Extbase_Persistence_ObjectStorage {

	/**
	 * @var callable
	 */
	protected $hasher;

	/**
	 * @var bool
	 */
	protected $hasCustomHash = TRUE;

	/**
	 * @param string|array|callable $hasher
	 */
	public function __construct($hasher = NULL) {
		if (is_string($hasher)) {
			$this->hasher = function($obj) use ($hasher) {
				return method_exists($obj, '_getProperty') ? $obj->_getProperty($hasher) : NULL;
			};
		} else if (is_array($hasher) && isset($hasher['method'])) {
			$method = $hasher['method'];
			$this->hasher = function($obj) use ($method) {
				return method_exists($obj, $method) ? $obj->$method() : NULL;
			};
		} else if (is_callable($hasher)) {
			$this->hasher = $hasher;
		} else {
			$this->hasher = "spl_object_hash";
			$this->hasCustomHash = FALSE;
		}
	}

	/**
	 * Associates data to an object in the storage. offsetSet() is an alias of attach().
	 *
	 * @param mixed $ignored We aren't using the offset here. Example: $storage[3] = $obj won't break if $obj->uid != 3.
	 * @param object $object The object to add.
	 * @return void
	 */
	public function offsetSet($ignored, $object) {
		$this->isModified = TRUE;
		$hash = $this->hash($object);
		$this->storage[$hash] = $object;
	}

	/**
	 * Checks whether an object exists in the storage.
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->storage[$offset]);
	}

	/**
	 * Removes an object from the storage. offsetUnset()
	 *
	 * @param mixed $offset The object to remove.
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->isModified = TRUE;
		unset($this->storage[$offset]);
	}

	/**
	 * Returns the data associated with an object.
	 *
	 * @param mixed $offset $object The object to look for.
	 * @return object The object at the given offset
	 */
	public function offsetGet($offset) {
		return $this->storage[$offset];
	}

	/**
	 * Checks if the storage contains the object provided.
	 *
	 * @param Object $object The object to look for.
	 * @return boolean Returns TRUE if the object is in the storage, FALSE otherwise.
	 */
	public function contains($object) {
		$offset = $this->hash($object);
		return $this->offsetExists($offset);
	}

	/**
	 * Adds an object in the storage, and optionaly associate it to some data.
	 *
	 * @param object $object The object to add.
	 * @return void
	 */
	public function attach($object) {
		$this->offsetSet('', $object);
	}

	/**
	 * Removes an object from the storage.
	 *
	 * @param object $object The object to remove.
	 * @return void
	 */
	public function detach($object) {
		$this->offsetUnset($this->hash($object));
	}

	/**
	 * Returns the current storage entry.
	 *
	 * @return object The object at the current iterator position.
	 */
	public function current() {
		return current($this->storage);
	}

	/**
	 * Returns this object storage as an array
	 *
	 * @return array The object storage
	 */
	public function toArray() {
		return $this->hasCustomHash ? $this->storage : array_values($this->storage);
	}

	/**
	 * Adds all objects-data pairs from a different storage in the current storage.
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $storage The storage you want to import.
	 * @return void
	 */
	public function addAll(Tx_Extbase_Persistence_ObjectStorage $storage) {
		foreach ($storage as $object) {
			$this->attach($object);
		}
	}

	/**
	 * @param mixed $info
	 * @throws \BadMethodCallException
	 */
	public function setInfo($info) {
		throw new \BadMethodCallException('HashStorage does not support object meta information.');
	}

	/**
	 * @throws \BadMethodCallException
	 */
	public function getInfo() {
		throw new \BadMethodCallException('HashStorage does not support object meta information.');
	}



	/**
	 * @param $obj
	 * @return mixed
	 */
	protected function hash($obj) {
		$hasher = $this->hasher;
		return $hasher($obj);
	}
}
?>