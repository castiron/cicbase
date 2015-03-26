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

	/** @test */
	public function itChecksKeysHaveValues() {
		$arr = array('one' => '', 'two' => 3);
		$this->assertFalse(Arr::hasValuesForKeys($arr, array_keys($arr)));
	}

	/** @test */
	public function itChecksKeysHaveValuesMissingKeys() {
		$arr = array('one' => '', 'two' => 3);
		$this->assertFalse(Arr::hasValuesForKeys($arr, array('one', 'two', 'three')));
	}

	/** @test */
	public function itChecksKeysHaveValuesIncludingSpaces() {
		$arr = array('one' => '    ', 'two' => 3);
		$this->assertFalse(Arr::hasValuesForKeys($arr, array_keys($arr)));
	}

	/** @test */
	public function itConvertsAssocArraysToStdClass() {
		$arr = array('a' => 1, 'b' => 3);
		$obj = Arr::toStdClass($arr);
		$this->assertEquals(3, $obj->b);
	}

	/** @test */
	public function itConvertsMultiAssocArraysToStdClass() {
		$arr = array('a' => 1, 'b' => array('c' => 4));
		$obj = Arr::toStdClass($arr);
		$this->assertEquals(4, $obj->b->c);
	}

	/** @test */
	public function itConvertsMultiAssocArraysToStdClassLeavingIndexedArraysAsArrays() {
		$arr = array('a' => 1, 'b' => array('c' => 4), 'd' => array(1 => 'e', '2' => 'f'));
		$obj = Arr::toStdClass($arr);
		$this->assertTrue(is_array($obj->d));
	}

	/** @test */
	public function itConvertsStdClassToArray() {
		$obj = new \stdClass();
		$obj->a = 1;
		$obj->b = 3;
		$arr = Arr::fromStdClass($obj);
		$this->assertEquals(3, $arr['b']);
	}

	/** @test */
	public function itConvertsDeepStdClassToArray() {
		$obj = new \stdClass();
		$obj->a = 1;
		$obj->b = new \stdClass();
		$obj->b->c = 4;
		$arr = Arr::fromStdClass($obj);
		$this->assertEquals(4, $arr['b']['c']);
	}

	/** @test */
	public function itFindsFirstValue() {
		$arr = array('a' => 3, 'b' => 4, 'c' => 5, 'd' => 6);
		$func = function ($key, $val) { return $val % 2 == 0; };
		$this->assertEquals(4, Arr::find($arr, $func));
	}

	/** @test */
	public function itFailsWhenFindingAndNothingFound() {
		$arr = array('a' => 3, 'b' => 4, 'c' => 5, 'd' => 6);
		$func = function ($key, $val) { return $val % 7 == 0; };
		$this->assertEquals(-1, Arr::find($arr, $func));
	}

	/** @test */
	public function itFindsFirstValueUsingKey() {
		$arr = array('a' => 3, 'b' => 4, 'c' => 5, 'd' => 6);
		$func = function ($key) { return in_array($key, array('b','d')); };
		$this->assertEquals(4, Arr::find($arr, $func));
	}

	/** @test */
	public function itFailsWhenFindingAndNothingFoundUsingKey() {
		$arr = array('a' => 3, 'b' => 4, 'c' => 5, 'd' => 6);
		$func = function ($key) { return in_array($key, array('e','f')); };
		$this->assertEquals(-1, Arr::find($arr, $func));
	}

	/** @test */
	public function itDeterminesIsIndexed() {
		$arr = array(1 => 'one', "2" => 'two');
		$this->assertTrue(Arr::isIndexed($arr));
	}

	/** @test */
	public function itDeterminesNotIndexed() {
		$arr = array(1 => 'one', "two" => 2);
		$this->assertFalse(Arr::isIndexed($arr));
	}

	/** @test */
	public function itDeterminesIsAssociative() {
		$arr = array('one' => 1, "two" => 2);
		$this->assertTrue(Arr::isAssoc($arr));
	}

	/** @test */
	public function itDeterminesNotAssociative() {
		$arr = array(1 => 'one', "two" => 2);
		$this->assertFalse(Arr::isAssoc($arr));
	}

	/** @test */
	public function itWalksKeysRecursively() {
		$arr      = array('one.' => 1, 'more.' => array('two.' => 2));
		$expected = array('one'  => 1, 'more'  => array('two'  => 2));
		$func = function ($key) { return rtrim($key, '.'); };
		Arr::walkKeysRecursive($arr, $func);
		$this->assertEquals($expected, $arr);
	}

	/** @test */
	public function itRemovesByKeys() {
		$arr = array('one' => 1, 'two' => 2, 'three' => 3);
		$expected = array('one' => 1, 'three' => 3);
		$this->assertEquals($expected, Arr::removeByKeys($arr, array('two')));
	}

	/** @test */
	public function itRemovesByKeysEvenIfEmpty() {
		$this->assertEquals(array(), Arr::removeByKeys(array(), array('two')));
	}

	/** @test */
	public function itSafelyFindsArrayValues() {
		$this->assertEquals(3, Arr::safe(array('three' => 3), 'three'));
	}

	/** @test */
	public function itSafelyFindsArrayValuesRecursively() {
		$arr = array('three' => array('four' => array('five' => 3)));
		$this->assertEquals(3, Arr::safe($arr, array('three', 'four', 'five')));
	}

	/** @test */
	public function itSafelyFailsToFindArrayValuesRecursively() {
		$arr = array('three' => array('four' => array('six' => 3)));
		$this->assertEquals('oops', Arr::safe($arr, array('three', 'four', 'five'), 'oops'));
	}

	/** @test */
	public function itSafelyFailsToFindArrayValuesRecursivelyAgain() {
		$arr = array('three' => array('four' => array('six' => array('five' => 3))));
		$this->assertEquals('oops', Arr::safe($arr, array('three', 'four', 'five'), 'oops'));
	}

	/** @test */
	public function itSafelyFailsToFindArrayValuesRecursivelyAgainAgain() {
		$arr = array('three' => array('four' => 4));
		$this->assertEquals('oops', Arr::safe($arr, array('three', 'four', 'five'), 'oops'));
	}

	/** @test */
	public function itSafelyFindsNestedArrayWithinArray() {
		$arr = array('me' => array('dad' => array('grampa' => array('great-gramp' => "Joe"))));
		$grampa = array('grampa' => array('great-gramp' => "Joe"));
		$this->assertEquals($grampa, Arr::safe($arr, array('me','dad')));
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