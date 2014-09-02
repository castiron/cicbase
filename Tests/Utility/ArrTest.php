<?php

namespace CIC\Cicbase\Tests\Utility;

use \CIC\Cicbase\Utility\Arr;

class ArrTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/** @var array */
	protected $arr;

	public function setUp() {
		$this->arr = array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
			'f' => 6,
		);
	}

	/** @test */
	public function itSelectsRandomItems() {
		$this->checkForNewResultsEachTime(function() {
			return Arr::selectRand($this->arr, 3);
		});
	}

	/** @test */
	public function itSelectsRandomItemsWithCorrectCount() {
		$actual = Arr::selectRand($this->arr, 3);
		$this->assertCount(3, $actual);
	}

	/** @test */
	public function itSelectsRandomItemsWithRightKeys() {
		$actual = Arr::selectRand($this->arr, 3);
		$this->assertArrayHasKey(key($actual), $this->arr);
	}

	/** @test */
	public function itSelectsRandomItemReturningItemIfCountIsOne() {
		$actual = Arr::selectRand($this->arr, 1);
		$this->assertTrue(is_numeric($actual));
	}

	/** @test */
	public function itShufflesAssociativeArrays() {
		$this->checkForNewResultsEachTime(function() {
			return Arr::shuffleAssoc($this->arr);
		});
	}

	/** @test */
	public function itShufflesAssociativeArraysWithRightKeys() {
		$actual = Arr::shuffleAssoc($this->arr);
		$this->assertArrayHasKey(key($actual), $this->arr);
	}

	/** @test */
	public function itSplicesWithCallbacksWithCorrectResultsCount() {
		$results = Arr::spliceWhere($this->arr, function($val, $key) {
			return $key == 'b' || $val == 4;
		});
		$this->assertCount(2, $results);
	}

	/** @test */
	public function itSplicesWithCallbacksWithCorrectLeftoverCount() {
		$results = Arr::spliceWhere($this->arr, function($val, $key) {
			return $key == 'b' || $val == 4;
		});
		$this->assertCount(4, $this->arr);
	}

	/** @test */
	public function itSplicesWithCallbacksReturningEarly() {
		$iterations = 0;
		$results = Arr::spliceWhere($this->arr, function($val, $key) use (&$iterations) {
			return $val > 3 ? -1 : ++$iterations;
		});
		$this->assertEquals(3, $iterations);
	}

	/** @test */
	public function itSplicesWithCallbacksUsingLimits() {
		$iterations = 0;
		$results = Arr::spliceWhere($this->arr, function($val, $key) use (&$iterations) {
			return TRUE;
		}, 3);
		$this->assertCount(3, $results);
	}




	protected function checkForNewResultsEachTime(callable $callable) {
		$sameCount = $diffCount = 0;
		$old = $callable();
		for($i = 0; $i < 1000; ++$i) {
			$new = $callable();
			if ($old === $new) {
				++$sameCount;
			} else {
				++$diffCount;
			}
			$old = $new;
		}
		$this->assertGreaterThan($sameCount, $diffCount);
	}
}