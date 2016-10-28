<?php namespace CIC\Cicbase\Domain\Model;

/**
 * Class AbstractArrayBasedModel
 * @package CIC\Cicbase\Domain\Model
 */
class AbstractArrayBasedModel {
    /**
     * @var array
     */
    var $rec;

    /**
     * @param $name
     * @param $arguments
     * @return mixed|void
     */
    public function __call($name, $arguments) {
        /**
         * Has the method been defined?
         */
        if (method_exists($this, $name)) {
            return;
        }

        /**
         * Does the method start with 'get'?
         */
        if (strlen($name) > 3 && strpos($name, 'get') !== 0) {
            return;
        }

        $field = lcfirst(substr($name, 3));
        if (array_key_exists($field, $this->rec)) {
            return $this->rec[$field];
        }

    }
}
