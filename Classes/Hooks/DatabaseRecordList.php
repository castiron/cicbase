<?php

namespace CIC\Cicbase\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseRecordList implements \TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface {
	/**
	 * modifies the DB list query
	 *
	 * @param string $table The current database table
	 * @param integer $pageId The record's page ID
	 * @param string $additionalWhereClause An additional WHERE clause
	 * @param string $selectedFieldsList Comma separated list of selected fields
	 * @param \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList $parentObject Parent localRecordList object
	 * @return void
	 */
	public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
		if ($table == 'tx_cicbase_domain_model_file') {
			$selectedFieldsList = GeneralUtility::rmFromList('awslink', $selectedFieldsList);
		}
	}

}