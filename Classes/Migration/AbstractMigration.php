<?php
namespace CIC\Cicbase\Migration;

abstract class AbstractMigration implements MigrationInterface {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $db;

	public function __construct() {
		$this->db = $GLOBALS['TYPO3_DB'];
	}

	public function run() {
	}

	public function canRollback() {
		if(method_exists($this, 'rollback')) {
			return true;
		} else {
			return false;
		}
	}

}

?>