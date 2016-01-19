<?php

namespace CIC\Cicbase\Tests\Persistence\Storage;

class MigrationTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/** @var \TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
	protected $migration;

	public function setUp() {
		$this->migration = $this->getAccessibleMockForAbstractClass('CIC\Cicbase\Tests\Persistence\Storage\SampleTestMigration');
	}

	public function tearDown() {
		unset($this->migration);
	}

	/** @test */
	public function itTestsForTableExistence() {
		$this->assertTrue($this->migration->_call('tableExists', 'sys_language'));
	}

	/** @test */
	public function itTestsForMultipleTableExistence() {
		$this->assertTrue($this->migration->_call('tablesExist', array('sys_language','fe_users')));
	}

	/** @test */
	public function itTestsForTableExistenceNegative() {
		$this->assertFalse($this->migration->_call('tableExists', 'blah_no_way_this_exists'));
	}

	/** @test */
	public function itTestsForMultipleTableExistenceNegative() {
		$this->assertFalse($this->migration->_call('tablesExist', array('sys_language', 'nor_this_one')));
	}

	/** @test */
	public function itTestsForColumnExistence() {
		$this->assertTrue($this->migration->_call('columnExists', 'fe_users', 'username'));
	}

	/** @test */
	public function itTestsForMultipleColumnExistence() {
		$this->assertTrue($this->migration->_call('columnsExist', 'fe_users', array('username','address')));
	}

	/** @test */
	public function itTestsForColumnExistenceNegative() {
		$this->assertFalse($this->migration->_call('columnExists', 'fe_users', 'blah_no_way_this_exists'));
	}

	/** @test */
	public function itTestsForMultipleColumnExistenceNegative() {
		$this->assertFalse($this->migration->_call('columnsExist', 'fe_users', array('username', 'nor_this_one')));
	}
}

class SampleTestMigration extends \CIC\Cicbase\Migration\AbstractMigration {
	public function run() {}
}