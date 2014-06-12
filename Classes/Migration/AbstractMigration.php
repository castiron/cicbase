<?php
namespace CIC\Cicbase\Migration;

abstract class AbstractMigration implements MigrationInterface {

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