<?php namespace CIC\Cicbase\Traits;

/**
 * Class Saveable
 * @package CIC\Cicbase\Traits
 */
trait Saveable {
    use Database;

    public function save() {
        $query = static::UPSERTquery(static::$table, $this->saveFields());
        static::db()->sql_query($query);
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
}
