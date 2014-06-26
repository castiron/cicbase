<?php

namespace CIC\Cicbase\Tests\Persistence\Storage;

class HashStorageTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	protected $models = array();

	public function setUp() {
		if (!count($this->models)) {
			$this->makeModels();
		}
	}

	/** @test */
	public function itUsesPropertyHashing() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		$this->fillStorage($storage);
		$this->assertSame($this->models[0], $storage[1]);
	}

	/** @test */
	public function itUsesMethodHashing() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage(array('method' => 'getUpperCaseTitle'));
		$this->fillStorage($storage);
		$this->assertSame($this->models[0], $storage['SOME TITLE']);
	}

	/** @test */
	public function itUsesCallbackHashing() {
		$callback = function($obj) { return $obj->getTitle(); };
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage($callback);
		$this->fillStorage($storage);
		$this->assertSame($this->models[1], $storage['Another Title']);
	}

	/** @test */
	public function itSetsWithArrayBrackets() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		foreach ($this->models as $model) {
			$storage[] = $model;
		}
		$this->assertSame($this->models[0], $storage[1]);
	}

	/** @test */
	public function itSetsWithArrayBracketsIgnoringKey() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		foreach ($this->models as $model) {
			$storage[$model->getUpperCaseTitle()] = $model;
		}
		$this->assertSame($this->models[0], $storage[1]);
	}

	/** @test */
	public function itIsIterable() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		$this->fillStorage($storage);
		foreach ($storage as $uid => $model) /* nix */ ;
		$this->assertSame(array(2, $this->models[1]), array($uid, $model));
	}

	/** @test */
	public function itIsCountable() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage();
		$this->fillStorage($storage);
		$this->assertEquals(2, count($storage));
	}

	/** @test */
	public function itProvidesRawArray() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage();
		$this->fillStorage($storage);
		$this->assertSame($this->models, $storage->toArray());
	}

	/** @test */
	public function itProvidesRawArrayWithHashKeys() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		$this->fillStorage($storage);
		$ourModels = array();
		foreach ($this->models as $model) {
			$ourModels[$model->getUid()] = $model;
		}
		$this->assertSame($ourModels, $storage->toArray());
	}

	/** @test */
	public function itHandlesUnsettingItems() {
		$storage = new \CIC\Cicbase\Persistence\Storage\HashStorage('uid');
		$this->fillStorage($storage);
		unset($storage[2]);
		$this->assertNull($storage[2]);
	}

	/** @test */
	public function itMaintainsObjectPositionsIndependentFromInternalStorage() {
		$this->markTestIncomplete();
	}

	/**
	 * Adds our models to the storage
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $storage
	 */
	protected function fillStorage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $storage) {
		foreach ($this->models as $model) {
			$storage->attach($model);
		}
	}

	/**
	 * Makes an array of models to work with
	 */
	protected function makeModels() {
		$props = array(
			array('uid' => 1, 'title' => 'Some Title'),
			array('uid' => 2, 'title' => 'Another Title')
		);

		foreach ($props as $fields) {
			$model = new HashStorageTestModel();
			foreach ($fields as $prop => $value) {
				$model->_setProperty($prop, $value);
			}
			$this->models[] = $model;
		}
	}
}

class HashStorageTestModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
	protected $title = '';

	public function setStuff($uid, $title) {
		$this->uid = $uid;
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getUpperCaseTitle() {
		return strtoupper($this->title);
	}
}