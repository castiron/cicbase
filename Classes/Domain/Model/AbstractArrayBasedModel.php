<?php namespace CIC\Cicbase\Domain\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class AbstractArrayBasedModel
 * @package CIC\Cicbase\Domain\Model
 */
class AbstractArrayBasedModel {
    /**
     * @var array
     */
    var $rec = [];

    /**
     * The (lower camel case) names of any attrs that you want to be able to write to with `set` magic methods
     * @var array
     */
    protected $attrWritable = array();

    /**
     * You can specify valid fieldname prefixes here, like 'tx_myext_` or whatever.
     * That will tell the "get" and "set" magic methods to check for fields with those prefixes on them when
     * determining the underlying field to use.
     *
     * @var array
     */
    protected $fieldPrefixes = array();

    /**
     * @return mixed
     */
    public function getRec() {
        return $this->rec;
    }

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
        if (strlen($name) > 3 && strpos($name, 'get') === 0) {
            return $this->__getField($this->__methodToField($name));
        }

        /**
         * Does the method start with 'set'?
         */
        if (strlen($name) > 3 && strpos($name, 'set') === 0) {
            $this->__setField($this->__methodToField($name), $arguments[0]);
        }
    }

    /**
     * @param $methodName
     * @param int $cropCount The number of chars to drop from the beginning of the method name
     * @return mixed|string
     */
    protected function __methodToField($methodName, $cropCount = 3) {
        $fromCamelCase = GeneralUtility::camelCaseToLowerCaseUnderscored(
            lcfirst(substr($methodName, $cropCount))
        );

        /**
         * See about fetching this after converting from camelcase
         */
        if ($this->__hasField($fromCamelCase) || $this->__fieldIsWritable($fromCamelCase)) {
            return $fromCamelCase;
        }

        /**
         * Fallback for legacy setups (e.g. calls like $obj->getSome_field_by_name)
         */
        return lcfirst(substr($methodName, $cropCount));
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function __getField($field) {
        if (!is_array($this->rec)) {
            return null;
        }

        if (array_key_exists($field, $this->rec)) {
            return $this->rec[$field];
        }

        /**
         * If we didn't find any values based on the plain fieldname, let's try any valid prefixes
         * in the order they were declared.
         */
        foreach ($this->fieldPrefixes as $prefix) {
            $prefixed = $prefix . GeneralUtility::camelCaseToLowerCaseUnderscored($field);
            if (array_key_exists($prefixed, $this->rec)) {
                return $this->rec[$prefixed];
            }
        }
    }

    /**
     * @param string $field
     * @param mixed $value
     * @throws Exception
     */
    protected function __setField($field, $value) {
        if (!$this->__fieldIsWritable($field)) {
            throw new Exception("'$field' is not a writable attribute");
        }
        $this->rec[$field] = $value;
    }

    /**
     * @param $field
     * @return bool
     */
    protected function __fieldIsWritable($field) {
        /**
         * Are all fields writable?
         */
        if ($this->__allFieldsAreWritable()) {
            return true;
        }

        /**
         * Check if the field is allowed by configured attr_writable after accounting
         * for field prefixes (or no prefix, as the case may be).
         */
        foreach (array_merge([''], $this->fieldPrefixes) as $prefix) {
            if (in_array($prefix . $field, $this->attrWritable)) {
                return true;
            }
        }

        /**
         * No writable field found.
         */
        return false;
    }

    /**
     * @return bool
     */
    protected function __allFieldsAreWritable() {
        return $this->attrWritable[0] === '*' || $this->attrWritable === '*';
    }

    /**
     * @param $field
     * @return bool
     */
    protected function __hasField($field) {
        if (!is_array($this->rec)) {
            return false;
        }
        return array_key_exists($field, $this->rec);
    }
}
