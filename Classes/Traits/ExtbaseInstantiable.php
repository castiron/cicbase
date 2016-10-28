<?php namespace CIC\Cicbase\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtbaseInstantiable
 * @package CIC\Cicbase
 */
trait ExtbaseInstantiable {
    /**
     * @return self
     */
    public static function get() {
        $args = func_get_args();
        array_unshift($args, get_called_class());
        return call_user_func_array(
            array(static::objectManager(), 'get'),
            $args
        );
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager object
     */
    protected static function objectManager() {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }
}

