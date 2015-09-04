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

	/** @test */
	public function itMakesAStringToAWebUsablePath() {
		$tests = array(
			'street smarts/You-can-COUNT on/believe.png' => 'street_smarts/You-can-COUNT_on/believe.png',
			'/street_smarts/AND % true grit/  or-not' => '/street_smarts/AND__true_grit/__or-not',
			'/street_smarts/I was told ~\'" @ #you had grit!/that\'s why I hired you!' => '/street_smarts/I_was_told___you_had_grit/thats_why_I_hired_you',
			'life on the streets is tough' => 'life_on_the_streets_is_tough',
			'gimme_a-sandwich.or.i.will.cry' => 'gimme_a-sandwich.or.i.will.cry',
		);
		foreach($tests as $in => $out) {
			$this->assertEquals($out, Str::toWebUsablePath($in));
		}
	}

}
