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
        return static::objectManager()->get(get_called_class());
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager object
     */
    protected static function objectManager() {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }
}

