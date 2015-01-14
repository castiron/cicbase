<?php

namespace CIC\Cicbase\Tests\Utility;

use \CIC\Cicbase\Utility\Str;

class StrTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/** @test */
	public function itMakesUnderscoreStringsToCamelCase() {
		$this->assertEquals('streetSmarts', Str::cCase('street_smarts'));
	}
	/** @test */
	public function itMakesUnderscoreStringsToCamelCaseWhenAlreadyCamelCase() {
		$this->assertEquals('streetSmarts', Str::cCase('streetSmarts'));
	}
	/** @test */
	public function itMakesUnderscoreStringsToCamelCaseWhenNotNeeded() {
		$this->assertEquals('street', Str::uCase('street'));
	}

	/** @test */
	public function itMakesCamelCaseStringsToUnderscore() {
		$this->assertEquals('street_smarts', Str::uCase('streetSmarts'));
	}
	/** @test */
	public function itMakesCamelCaseStringsToUnderscoreWhenAlreadyCamelCase() {
		$this->assertEquals('street_smarts', Str::uCase('street_smarts'));
	}
	/** @test */
	public function itMakesCamelCaseStringsToUnderscoreWhenNotNeeded() {
		$this->assertEquals('street', Str::uCase('street'));
	}

}