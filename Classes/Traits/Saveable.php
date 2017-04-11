<?php namespace CIC\Cicbase\Traits;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class Saveable
 * @package CIC\Cicbase\Traits
 */
trait Saveable {
    use Database;

    /**
     * @return $this
     * @throws Exception
     */
    public function save() {
        $query = static::UPSERTquery(static::$table, $this->saveFields(), false, $this->excludeFromUpdateFields());
        static::db()->sql_query($query);
        if ($err = static::db()->sql_error()) {
            throw new Exception($err);
        }
        return $this;
    }

    /**
     *
     */
    public function destroy() {
        static::db()->exec_DELETEquery(static::$table, 'uid=' . intval($this->rec['uid']));
    }

    /**
     * @param $uid
     * @return array|FALSE|NULL
     */
    public function fetch($uid) {
        if (!$uid) {
            return;
        }
        return static::db()->exec_SELECTgetSingleRow('*', static::$table, 'uid=' . intval($uid));
    }

    /**
     * @return array
     */
    protected function saveFields() {
        return [];
    }

    /**
     * @return array
     */
    protected function excludeFromUpdateFields() {
        return [];
    }
}
