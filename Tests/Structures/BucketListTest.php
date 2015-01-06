<?php

namespace CIC\Cicbase\Tests\Structures;

use CIC\Cicbase\Structures\BucketList;

class BucketListTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {


	/** @var BucketList */
	protected $list;

	public function setUp() {
		$this->list = new BucketList(array(
			4,2,1,3,'d','a','c'
		));
	}

	/** @test */
	public function itWorksWhenEmpty() {
		foreach ($this->list as $item) {
			$this->fail("Empty list produced something");
		}
		$this->assertTrue(TRUE);
	}

	/** @test */
	public function itWorksWithOneItem() {
		$this->list->insert(1, 'hello');
		$items = array();
		foreach ($this->list as $item) {
			$items[] = $item;
		}
		$this->assertCount(1, $items);
		$this->assertEquals('hello', $items[0]);
	}

	/** @test */
	public function itWorksWithAddingItemsOutOfOrder() {
		$this->list->insert('a', 'one');
		$this->list->insert(1, 'two');
		$items = array();
		foreach ($this->list as $item) {
			$items[] = $item;
		}
		$this->assertCount(2, $items);
		$this->assertEquals('two', $items[0]);
	}

	/** @test */
	public function itReturnsTheAppropriateCurrentBucket() {
		$this->list->insert(1, 'alpha');
		$this->list->insert(1, 'beta');
		$this->list->insert(1, 'gamma');
		$this->list->insert(2, 'dreams');
		$this->list->insert(3, 'miracles');
		$buckets = array();
		foreach ($this->list as $item) {
			$buckets[] = $this->list->currentBucket();
		}
		$expected = array(2,1,1,1,3);
		$this->assertSame($expected, $buckets);
	}
}