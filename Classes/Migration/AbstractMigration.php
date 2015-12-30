<?php
namespace CIC\Cicbase\Migration;

/**
 * Class AbstractMigration
 * @package CIC\Cicbase\Migration
 */
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
        return method_exists($this, 'rollback');
    }

    /**
     * Copies the data from one table to another.
     * If the source table has more columns than the destination table,
     * the copy still happens just without that data being transferred.
     *
     * @param string $source
     * @param string $destination
     * @param array $renameColumns [sourceColName => destColName, ...] Needed if you're copying from one table to another where the column names differ
     * @return boolean
     */
    protected function copyTable($source, $destination, $renameColumns = array()) {
        $this->expectTables(array($source, $destination), "Can't copy table");
        $sourceCols = $this->fields($source);
        $destCols = $this->fields($destination);

        // Only copy columns that exist in both tables
        $columnsInSourceNotInDest = array_diff($sourceCols, $destCols);
        foreach ($columnsInSourceNotInDest as $unsetColumn) {
            if (!isset($renameColumns[$unsetColumn])) {
                $key = array_search($unsetColumn, $sourceCols);
                unset($sourceCols[$key]);
            }
        }

        // As long as we select the fields from the $source table that are in the $dest table,
        // we shouldn't run into any errors.
        $sourceSelects = array_combine($sourceCols, $sourceCols);

        // Rename any columns
        if (count($renameColumns)) {
            foreach ($renameColumns as $sourceCol => $destCol) {
                $this->expectColumn($source, $sourceCol, "When copying table $source to $destination, can't rename column $sourceCol to $destCol because $sourceCol does not exist.");
                $this->expectColumn($destination, $destCol, "When copying table $source to $destination, can't rename column $sourceCol to $destCol because $destCol does not exist.");
                if (!isset($sourceSelects[$sourceCol])) {
                    $this->log("Tried to rename a column that won't be used when copying table $source to $destination.");
                    continue;
                }
                $sourceSelects[$sourceCol] = "$sourceCol AS $destCol";
            }
        }

        $select = implode(', ', $sourceSelects);

        $sourceRows = $this->db->exec_SELECTgetRows($select, $source, '');
        if (!count($sourceRows)) {
            $this->success("Nothing to copy from $source to $destination");
            return TRUE;
        }

        $this->db->exec_INSERTmultipleRows($destination, array_keys($sourceRows[0]), $sourceRows);
        $this->success("Copied table from $source to $destination.");
        return TRUE;
    }

    /**
     * Copies a column into another column on the same or different table.
     * Fails if the columns don't exist in either table.
     *
     * @param string $table
     * @param string $sourceField
     * @param string $destinationField
     * @throws \Exception
     */
    protected function copyField($table, $sourceField, $destinationField) {
        $this->expectColumns($table, array($sourceField, $destinationField), "Can't copy $sourceField to $destinationField in $table");
        $this->db->exec_UPDATEquery($table, '', array($destinationField => $sourceField), array($destinationField));
        $this->success("Copied field in table $table from $sourceField to $destinationField");
        return;
    }

    /**
     * @param string $table
     * @param string $message
     * @throws \Exception
     */
    protected function expectTable($table, $message = '') {
        if (!$this->tableExists($table)) {
            $this->errorMsg = $message . "\n  Table doesn't exist: $table.";
            throw new \Exception();
        }
    }

    /**
     * @param array $tables
     * @param string $message
     * @throws \Exception
     */
    protected function expectTables(array $tables, $message = '') {
        if (!$this->tablesExist($tables)) {
            $this->errorMsg = $message . "\n  At least one of these tables doesn't exist: ".implode(', ', $tables).'.';
            throw new \Exception();
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $message
     * @throws \Exception
     */
    protected function expectColumn($table, $column, $message = '') {
        if (!$this->columnExists($table, $column)) {
            $this->errorMsg = $message . "\n  Column $column does not exist in table $table.";
            throw new \Exception();
        }
    }

    /**
     * @param $table
     * @param $fieldsString
     * @throws \Exception
     */
    protected function expectEmptyTable($table, $fieldsString = 'uid', $message = '') {
        if ($this->db->exec_SELECTcountRows($fieldsString, $table)) {
            $this->errorMsg = "$message\n  $table table already has data. Aborting migration.";
            throw new \Exception();
        }
    }

    /**
     * @param string $table
     * @param array $columns
     * @param string $message
     * @throws \Exception
     */
    protected function expectColumns($table, array $columns, $message = '') {
        if (!$this->columnsExist($table, $columns)) {
            $this->errorMsg = $message . "\n  Table $table is missing at least one of these columns: ".implode(', ', $columns).'.';
            throw new \Exception();
        }
    }


    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function columnExists($table, $column) {
        return $this->tableExists($table) && array_search($column, $this->fields($table)) !== FALSE;
    }

    /**
     * @param string $table
     * @param array $columns
     * @return bool
     */
    protected function columnsExist($table, array $columns) {
        $uColumns = array_unique($columns);
        $uColumnCount = count($uColumns);
        return $uColumnCount > 0 && $this->tableExists($table) && count(array_intersect($this->fields($table), $uColumns)) == $uColumnCount;
    }

    /**
     * @param string $table
     * @return bool
     */
    protected function tableExists($table) {
        return in_array($table, $this->tables());
    }

    /**
     * @param array $tables
     * @return bool
     */
    protected function tablesExist(array $tables) {
        $uTables = array_unique($tables);
        $uTableCount = count($uTables);
        return $uTableCount > 0 && count(array_intersect($this->tables(), $uTables)) == $uTableCount;
    }

    /**
     * @param string $table
     * @return array
     */
    protected function fields($table) {
        return array_keys($this->db->admin_get_fields($table));
    }

    /**
     * @return array
     */
    protected function tables() {
        if (!isset($this->_tables)) {
            $this->_tables = array_keys($this->db->admin_get_tables());
        }
        return $this->_tables;
    }

    /**
     * @param string $table
     * @return string
     */
    protected function safeTickQuoteName($table) {
        return '`' . str_replace('`', '\`', (string)$table) . '`';
    }

    /**
     * @param $table
     * @param $field
     * @param string $size
     * @throws \Exception
     */
    protected function addVarcharField($table, $field, $size = '255') {
        $size = intval($size);
        $this->expectTable($table, "Can't add varchar field '$field($size)' to missing table '$table'");
        $this->db->sql_query('ALTER TABLE ' . $this->safeTickQuoteName($table)
            . ' ADD ' . $this->safeTickQuoteName($field)
            . ' varchar(' . $size . ') default NULL;');
    }

    /**
     * @param $table
     * @param $field
     * @param int $default
     * @throws \Exception
     */
    protected function addTinyIntField($table, $field, $default = 0) {
        $default = intval($default);
        $this->expectTable($table, "Can't add tinyint field '$field' to missing table '$table'");
        $this->db->sql_query('ALTER TABLE ' . $this->safeTickQuoteName($table)
            . ' ADD ' . $this->safeTickQuoteName($field)
            . ' tinyint(4) NOT NULL default \'' . $default . '\'');
    }

    /**
     * @param $table
     * @param $field
     * @param int $size
     * @param int $default
     * @throws \Exception
     */
    protected function addIntField($table, $field, $size = 11, $default = 0) {
        $size = intval($size);
        $default = intval($default);
        $this->expectTable($table, "Can't add int field '$field($size)' to missing table '$table'");
        $this->db->sql_query('ALTER TABLE ' . $this->safeTickQuoteName($table)
            . ' ADD ' . $this->safeTickQuoteName($field)
            . ' int(' . $size . ') unsigned default \'' . $default . '\'');
    }

    /**
     * @param $table
     * @param $field
     * @throws \Exception
     */
    protected function addTextField($table, $field) {
        $this->expectTable($table, "Can't add text field '$field' to missing table '$table'");
        $this->db->sql_query('ALTER TABLE ' . $this->safeTickQuoteName($table)
            . ' ADD ' . $this->safeTickQuoteName($field) . ' text');
    }

    /**
     * @param $table
     * @param $field
     * @throws \Exception
     */
    protected function dropFieldFromTable($table, $field) {
        $this->expectColumn($table, $field, "Can't drop non-existent field '$field' from table '$table'");
        $this->db->sql_query('ALTER TABLE ' . $this->safeTickQuoteName($table) . ' DROP ' . $this->safeTickQuoteName($field) . ';');
    }

    /**
     * @param string $msg
     */
    protected function log($msg) {
        echo "  LOG: $msg\n";
    }

    /**
     * @param $msg
     */
    protected function success($msg) {
        echo "  SUCCESS: $msg\n";
    }

    /**
     * @return string
     */
    public function getErrorMsg() {
        return $this->errorMsg;
    }

}
