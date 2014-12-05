<?php

namespace CIC\Cicbase\Persistence;

use CIC\Cicbase\Utility\Arr;

class SQLLogger implements \TYPO3\CMS\Core\Database\PostProcessQueryHookInterface {

	/** @var string */
	protected static $lastSQL = array();

	public static function getLastQuery($table) {
		return Arr::safe(self::$lastSQL, $table);
	}

	protected static function save($table) {
		$tableAndJoins = explode(' ', $table);
		self::$lastSQL[$tableAndJoins[0]] = $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
	}

	public function exec_SELECTquery_postProcessAction(&$select_fields, &$from_table, &$where_clause, &$groupBy, &$orderBy, &$limit, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($from_table);
	}

	public function exec_INSERTquery_postProcessAction(&$table, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($table);
	}

	public function exec_INSERTmultipleRows_postProcessAction(&$table, array &$fields, array &$rows, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($table);
	}

	public function exec_UPDATEquery_postProcessAction(&$table, &$where, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($table);
	}

	public function exec_DELETEquery_postProcessAction(&$table, &$where, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($table);
	}

	public function exec_TRUNCATEquery_postProcessAction(&$table, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject) {
		self::save($table);
	}
}