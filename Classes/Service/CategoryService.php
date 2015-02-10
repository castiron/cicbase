<?php

namespace CIC\Cicbase\Service;

use CIC\Cicbase\Utility\Arr;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;

class CategoryService implements SingletonInterface {

	protected static $categoryMMTable = 'sys_category_record_mm';
	protected static $categoryTable = 'sys_category';

	/**
	 * @param int $uid
	 * @return array|FALSE|NULL
	 */
	public function findByUid($uid) {
		$select = '*';
		$where = "uid = $uid ". BackendUtility::deleteClause(self::$categoryTable);
		return $this->getDatabase()->exec_SELECTgetSingleRow($select, self::$categoryTable, $where);
	}

	/**
	 * @param string|array $uids
	 * @param mixed $valueColumn See Arr::column
	 * @param mixed $keyColumn See Arr::column
	 * @return array|NULL
	 * @throws \Exception
	 */
	public function findByUids($uids, $valueColumn = NULL, $keyColumn = NULL) {
		$select = '*';
		$uids = Arr::commaListToArray($uids, "CategoryService expected uids to be a comma list or an array");
		$where = "uid IN (".implode(',', $uids).') '. BackendUtility::deleteClause(self::$categoryTable);
		$rows = $this->getDatabase()->exec_SELECTgetRows($select, self::$categoryTable, $where, '', 'sorting ASC');
		if ($valueColumn || $keyColumn) {
			return Arr::column($rows, $valueColumn, $keyColumn);
		}

		return $rows;
	}




	/**
	 * Gets the database object.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}