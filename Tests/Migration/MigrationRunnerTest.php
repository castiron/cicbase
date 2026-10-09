<?php

namespace CIC\Cicbase\Tests\Migration;

use CIC\Cicbase\Migration\MigrationRunner;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class MigrationRunnerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/** @var \TYPO3\CMS\Core\Tests\AccessibleObjectInterface|MigrationRunner */
	protected $runner;

	/** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection */
	protected $db;

	protected $originalDb;

	protected $originalExecTime;

	public function setUp() {
		$this->originalDb = isset($GLOBALS['TYPO3_DB']) ? $GLOBALS['TYPO3_DB'] : null;
		$this->originalExecTime = isset($GLOBALS['EXEC_TIME']) ? $GLOBALS['EXEC_TIME'] : null;

		$this->db = $this->getMockBuilder(DatabaseConnection::class)
			->disableOriginalConstructor()
			->setMethods(['exec_INSERTquery', 'exec_DELETEquery', 'prepare_SELECTquery', 'fullQuoteStr'])
			->getMock();
		$GLOBALS['TYPO3_DB'] = $this->db;
		$GLOBALS['EXEC_TIME'] = 1538000000;

		$this->runner = $this->getAccessibleMock(MigrationRunner::class, ['dummy']);
		$this->runner->initializeObject();
		$this->runner->_set('currentRunExtKey', 'my_ext');
	}

	public function tearDown() {
		$GLOBALS['TYPO3_DB'] = $this->originalDb;
		$GLOBALS['EXEC_TIME'] = $this->originalExecTime;
		unset($this->runner, $this->db);
	}

	/** @test */
	public function itRecordsWhenAMigrationWasRun() {
		$this->db->expects($this->once())
			->method('exec_INSERTquery')
			->with('tx_cicbase_migrations', [
				'version' => '1402673404',
				'ext_key' => 'my_ext',
				'tstamp' => 1538000000,
			]);

		$this->runner->_call('saveVersion', 'AddSomeFields1402673404');
	}

	/** @test */
	public function itTakesTheTimestampFromTheCurrentExecTime() {
		$recorded = [];
		$this->db->method('exec_INSERTquery')
			->willReturnCallback(function ($table, array $fields) use (&$recorded) {
				$recorded[] = $fields['tstamp'];
			});

		$this->runner->_call('saveVersion', 'AddSomeFields1402673404');
		$GLOBALS['EXEC_TIME'] = 1538000100;
		$this->runner->_call('saveVersion', 'AddOtherFields1402673500');

		$this->assertSame([1538000000, 1538000100], $recorded);
	}

	/** @test */
	public function itStillRollsBackByVersionAndExtensionOnly() {
		$this->db->method('fullQuoteStr')->willReturnCallback(function ($str) {
			return "'" . $str . "'";
		});
		$this->db->expects($this->once())
			->method('exec_DELETEquery')
			->with('tx_cicbase_migrations', "version = 1402673404 AND ext_key = 'my_ext'");

		$this->runner->_call('removeVersion', 'AddSomeFields1402673404');
	}
}
