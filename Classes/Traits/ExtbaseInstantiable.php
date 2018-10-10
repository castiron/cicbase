<?php namespace CIC\Cicbase\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(...$args);
    }
}

