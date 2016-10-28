<?php namespace CIC\Cicbase\Traits;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class Database
 * @package CIC\Cicbase\Traits
 */
trait Database {
    /**
     * @return DatabaseConnection
     */
    protected static function db() {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param $table
     */
    protected static function enableFields($table) {
        return $GLOBALS['TSFE']->sys_page->enableFields($table);
    }
}
