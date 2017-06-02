<?php namespace CIC\Cicbase\Traits;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * You can apply this trait to any Extbase model class, and it will give you magic methods like
 *   $model->getSomeProperty();
 *   $model->setSomeProperty('val');
 *
 * without having to explicitly define those.
 *
 * NB: This won't work if applied to a class that doesn't walk like an Extbase Model
 *
 * Class ExtbaseModelAccessible
 * @package CIC\Cicbase\Traits
 */
trait ExtbaseModelAccessible implements DomainObjectInterface {
    protected $_attrWritable = [];
    protected $_attrReadable = [];
    protected $_attrAccessible = [];

    /**
     * "Magic" getters and setters
     *
     * @param $name
     * @param $arguments
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

        }

        /**
         * Does the method start with 'set'?
         */
        if (strlen($name) > 3 && strpos($name, 'set') === 0) {
            if ($fieldName = $this->__methodToField($name)) {
                $this->__setField($fieldName, $arguments[0]);
            }
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
        if ($this->_hasProperty($fromCamelCase) || $this->__fieldIsWritable($fromCamelCase)) {
            return $fromCamelCase;
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
        $this->_setProperty($field, $value);
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function __getField($field) {
        return $this->_getProperty($field);
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
        if (in_array($field, $this->_attrWritable)) {
            return true;
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
        return count($this->_attrWritable) === 0 || $this->_attrWritable[0] === '*' || $this->_attrWritable === '*';
    }
}
