<?php


/**
 * An easy way to mock up some domain objects.
 *
 * 1. Create a new $om.
 *
 * 2. Define some models:
 *
 * $om->def('Tx_SomeClass', 'someclass', [
 *   1 => ['title' => 'Title 1'],
 *   2 => ['title' => 'Another'],
 * ]);
 *
 * $om->def('Tx_AnotherClass', 'another', [
 *   1 => ['name' => 'Booger'],
 *   2 => ['name' => 'Woogie'],
 * ], [
 *   1 => ['someclass' => ['somes' => [1,2]]]
 * ]);
 *
 * We've defined 4 instances. And the "another" object
 * with ID of "1" has a reference to 2 "someclass" instances
 * on the "somes" field.
 *
 * 3. Get your instances.
 *
 * $x = $om->get('another', 1);
 *
 */
class Tx_Cicbase_Utility_ObjectMaker {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	protected $objects = array();

	protected $relations = array();

	/**
	 * @param string $class The name of the class you're defining
	 * @param string $nick A possible nick name (for referencing later)
	 * @param array $attrs An array of arrays of attributes for multiple instances of the object
	 * @param array $relations An array of arrays of other objects this one is going to reference
	 * @return $this
	 */
	public function def($class, $nick = null, $attrs = array(), $relations = array()) {
		list($class, $nick, $attrs, $relations) = $this->parseDefArgs($class, $nick, $attrs, $relations);

		$currentObjects = array();
		foreach ($attrs as $id => $attrArray) {
			$obj = $this->objectManager->get($class);
			$obj->_setProperty('uid', $id);
			foreach ($attrArray as $prop => $val) {
				$obj->_setProperty($prop, $val);
			}
			$currentObjects[$id] = $obj;
		}

		$this->objects[$nick] = $currentObjects;
		$this->relations[$nick] = $relations;

		return $this;
	}

	/**
	 * @param string $nick
	 * @param integer $id
	 * @return mixed
	 * @throws Exception
	 */
	public function get($nick, $id) {
		$key = array($nick, $id);
		$obj = Tx_Cicbase_Utility_Arr::safe($this->objects, $key);
		if (!$obj) {
			throw new Exception("Could not find an object for $nick with id $id");
		}
		$relations = Tx_Cicbase_Utility_Arr::safe($this->relations, $key);
		if ($relations) {

			foreach($relations as $otherNick => $data) {
				foreach ($data as $prop => $otherIDs) {

					// todo prevent circles

					if (is_array($otherIDs)) {
						/** @var Tx_Extbase_Persistence_ObjectStorage $others */
						$others = $this->objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
						foreach ($otherIDs as $otherID) {
							$others[$this->get($otherNick, $otherID)] = null;
						}
						$obj->_setProperty($prop, $others);
					} else {
						$obj->_setProperty($prop, $this->get($otherNick, $otherIDs));
					}

				}
			}
		}
		return $obj;
	}

	/**
	 * @param $class
	 * @param null $nick
	 * @param array $attrs
	 * @param array $relations
	 * @return array
	 */
	protected function parseDefArgs($class, $nick = null, $attrs = array(), $relations = array()) {
		switch(func_num_args()) {
			case 3:
				$nick = $class;
				$args = func_get_args();
				$relations = array_pop($args);
				$attrs = array_pop($args);
				break;
			case 4:
				if (!$nick) $nick = $class;
				break;
			default:
				throw new InvalidArgumentException("Invalid number of arguments");
		}

		if (!class_exists($class)) {
			throw new InvalidArgumentException("Class $class does not exist");
		}
		return array($class, $nick, $attrs, $relations);
	}


	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $om
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $om) {
		$this->objectManager = $om;
	}

}