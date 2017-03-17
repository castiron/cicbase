<?php namespace CIC\Cicbase\Traits;
use CIC\Cicbase\Utility\Arr;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class Database
 * @package CIC\Cicbase\Traits
 */
trait Database {
    use FrontendInstantiating;

    /**
     * @return DatabaseConnection
     */
    protected static function db() {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param $table
     * @param string $alias
     * @return string
     */
    protected static function enableFields($table, $alias = '') {
        if (static::isBackend()) {
            return static::aliasTableInQuery(BackendUtility::BEenableFields($table), $table, $alias);
        }
        static::initializeFrontend();
        $out = $GLOBALS['TSFE']->sys_page->enableFields($table);
        return static::aliasTableInQuery($out, $table, $alias);
    }

    /**
     * @param string $query
     * @param string $table
     * @param string $alias
     * @return mixed|string
     */
    protected static function aliasTableInQuery($query = '', $table = '', $alias = '') {
        return $alias ? str_replace("$table.", "$alias.", $query) : $query;
    }

    /**
     * @return bool
     */
    protected static function isBackend() {
        return TYPO3_MODE === 'BE';
    }

    /**
     * Cobbled from DatabaseConnection
     *
     * @param $table
     * @param $fields_values
     * @param bool $no_quote_fields
     * @param array $excludeFromUpdate Fields to exclude from the update statement if the record exists
     * @return null|string
     */
    public static function UPSERTquery($table, $fields_values, $no_quote_fields = FALSE, $excludeFromUpdate = array()) {
        /**
         * Table and fieldnames should be "SQL-injection-safe" when supplied to this
         * function (contrary to values in the arrays which may be insecure).
         */
        if (!is_array($fields_values) || count($fields_values) === 0) {
            return null;
        }

        /**
         * Quote and escape values
         */
        $fields_values = static::db()->fullQuoteArray($fields_values, $table, $no_quote_fields, true);
        $query = 'INSERT INTO ' . $table . ' (' . implode(',', array_keys($fields_values)) . ') VALUES ' . '(' . implode(',', $fields_values) . ')';

        /**
         * Hopefully add the duplicate key clause
         */
        $update_fields_values = Arr::removeByKeys($fields_values, $excludeFromUpdate);
        if ($update = static::updateClause($update_fields_values)) {
            $query .= ' ON DUPLICATE KEY UPDATE ' . $update;
        }

        return $query;
    }

    /**
     * @param $fieldsValues
     * @return string
     */
    protected static function updateClause($fieldsValues) {
        $out = array();
        foreach ($fieldsValues as $key => $val) {
            $out[] = "$key=$val";
        }
        return implode(',', $out);
    }

    /**
     * @param boolean|\mysqli_result|object $res
     * @return array
     */
    protected static function fetchRows($res) {
        $out = array();
        while ($row = static::db()->sql_fetch_assoc($res)) {
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Proxy to \TYPO3\CMS\Core\Database\DatabaseConnection::exec_SELECTquery that also fetches the results into and
     * returns an array
     * @param $select_fields
     * @param $from_table
     * @param string|array $where_clause
     * @param string $groupBy
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    protected static function selectArray($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '') {
        $where = is_array($where_clause) ? static::buildWhereClause($where_clause) : $where_clause;
        $select = is_array($select_fields) ? implode(',', $select_fields) : $select_fields;
        return static::fetchRows(static::db()->exec_SELECTquery(
            $select,
            $from_table,
            $where,
            $groupBy,
            $orderBy,
            $limit
        ));
    }

    /**
     *
     *
     * @param array $whereArray A nested array of conditions like
     * [
     *   'pid=12',
     *   'deleted=0',
     * ]
     *
     * or like
     *
     * [
     *   'AND' => [
     *     'pid=12', 'deleted=0'
     *      'OR' => [
     *         't3ver_wsid > 0',
     *         'something_else IN (1,2,3)',
     *      ]
     *   ]
     * ]
     *
     * @param string $conjunction The conjunction to use for the top set of conditions. Can be "AND" or "OR"
     * @return string
     * @throws Exception
     */
    protected static function buildWhereClause($whereArray = [], $conjunction = 'AND') {
        $staged = '';

        $validConjunction = function($val) {
            return $val === 'AND' || $val === 'OR';
        };

        /**
         * Do a little quick validation on conjunction
         */
        if (!$validConjunction($conjunction)) {
            throw new Exception('Invalid conjunction provided');
        }

        foreach ($whereArray as $k => $value) {
            if (is_array($value)) {
                $staged[] = '(' . static::buildWhereClause($value, $k) . ')';
            } else {
                $staged[] = $value;
            }
        }

        return count($staged) ? implode(" $conjunction ", $staged) : '';
    }
}
