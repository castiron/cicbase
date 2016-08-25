<?php namespace CIC\Cicbase\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtbaseInstantiable
 * @package CIC\Cicbase
 */
trait ExtbaseInstantiable {
    /**
     * @return object
     */
    public static function get() {
        $params = func_get_args();
        array_unshift($params, static::class);
        return call_user_func_array([static::objectManager(), 'get'], $params);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager object
     */
    protected static function objectManager() {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }
}

