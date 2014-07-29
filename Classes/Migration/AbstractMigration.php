<?php
namespace CIC\Cicbase\Migration;

abstract class AbstractMigration implements MigrationInterface {

	/** @var \TYPO3\CMS\Core\Database\DatabaseConnection */
	protected $db;

	/** @var string */
	protected $errorMsg = '';

	public function __construct() {
		$this->db = $GLOBALS['TYPO3_DB'];
	}

	abstract public function run();

	/**
	 * @return bool
	 */
	public function canRollback() {
		if(method_exists($this, 'rollback')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getErrorMsg() {
		return $this->errorMsg;
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return bool
	 */
	protected function columnExists($table, $column) {
		if (!$this->tableExists($table)) {
			return FALSE;
		}
		$fields = $this->db->admin_get_fields($table);
		return array_key_exists($column, $fields);
	}

	/**
	 * @param string $table
	 * @return bool
	 */
	protected function tableExists($table) {
		$tables = $this->db->admin_get_tables();
		return array_key_exists($table, $tables);
	}
}

?>