<?php namespace CIC\Cicbase\Traits;

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class FrontendInstantiating
 * @package CIC\Cicbase\Traits
 */
trait FrontendInstantiating {
    /**
     * @return bool
     */
    protected static function tsfeInitialized() {
        return

            /**
             * It exists
             */
            $GLOBALS['TSFE']

            /**
             * It's the right object
             */
            && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController;
    }

    /**
     * Cal this to ensure that the frontend is instantiated
     */
    protected static function initializeFrontend() {
        global $TYPO3_CONF_VARS;

        /**
         * Some frontend classes use this wacky time tracker and expect it to exist :/
         */
        if (!$GLOBALS['TT']) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker();
        }

        /**
         * Maybe there isn't a TSFE on here
         */
        if (!static::tsfeInitialized()) {
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
                $TYPO3_CONF_VARS,
                GeneralUtility::_GP('id'),
                0
            );
        }

        /**
         * Maybe there's no fe_user on that TSFE
         */
        if (!static::userSessionExists()) {
            $GLOBALS['TSFE']->initFEuser();
        }

        /**
         * Load the TCA over here
         */
        static::getBootstrap()->loadCachedTca();
        if (!is_object($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = static::initSysPage();
        }

        /**
         * Init TSFE->tmpl if need-be
         */
        if (!is_object($GLOBALS['TSFE']->tmpl)) {
            /** @var TypoScriptFrontendController $tsfe */
            $tsfe = $GLOBALS['TSFE'];
            $tsfe->initTemplate();
        }

        /**
         * This is needed if we're trying to use RealURL :(
         */
        if (!is_array($GLOBALS['TSFE']->config) || count($GLOBALS['TSFE']->config) === 0) {
            /**
             * This is needed for one of the subsequent TSFE or RealURL calls
             */
            if (!$GLOBALS['TCA']) {
                \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
            }
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->getConfigArray();
        }
    }

    /**
     * @return Bootstrap
     */
    protected static function getBootstrap() {
        return Bootstrap::getInstance();
    }

    /**
     * @return PageRepository
     */
    public static function sysPage() {
        static::initializeFrontend();
        return $GLOBALS['TSFE']->sys_page;
    }

    /**
     * @return PageRepository
     */
    protected static function initSysPage() {
        /** @var PageRepository $out */
        $out = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $out->init(false);
        return $out;
    }

    /**
     * @return bool
     */
    protected static function userSessionExists() {
        return $GLOBALS['TSFE'] && $GLOBALS['TSFE']->fe_user && $GLOBALS['TSFE']->fe_user->user['uid'];
    }
}

