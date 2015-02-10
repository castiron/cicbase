<?php

namespace CIC\Cicbase\Tests\Structures;

use CIC\Cicbase\Structures\Set;

class SetTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {


	/** @var Set */
	protected $set;

	public function setUp() {
		$this->set = new Set(array(
			4,2,1,3,'d','a','c'
		));
	}

	/** @test */
	public function itWorksWhenEmpty() {
		$this->set = new Set();
		foreach ($this->set as $item) {
			$this->fail("Empty list produced something");
		}
		$this->assertTrue(TRUE);
	}

	/** @test */
	public function itDoesntAddSameItems() {
		$before = $this->set->toArray();
		$this->set->add(array(2,1,'a'));
		$after = $this->set->toArray();
		$this->assertSame($before, $after);
	}

	/** @test */
	public function itAddsNewItems() {
		$before = $this->set->toArray();
		$this->set->add(array(2,1,'a', 'e'));
		$after = $this->set->toArray();
		$this->assertNotSame($before, $after);
	}

}