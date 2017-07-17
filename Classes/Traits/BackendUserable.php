<?php namespace CIC\Cicbase\Traits;

/**
 * Class BackendUserable
 * @package CIC\Cicbase\Traits
 */
trait BackendUserable {
    /**
     * @return \TYPO3\CMS\Backend\FrontendBackendUserAuthentication|null
     */
    public static function initBackendUser() {
        if (!$GLOBALS['BE_USER']) {
            $GLOBALS['BE_USER'] = $GLOBALS['TSFE']->initializeBackendUser();
        }
        return $GLOBALS['BE_USER'] ?: null;
    }

    /**
     * @return bool
     */
    public static function backendUserIsAuthenticated() {
        static::initBackendUser();
        return $GLOBALS['BE_USER'] ? true : false;
    }
}
