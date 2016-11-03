<?php namespace CIC\Cicbase\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class FrontendInstantiating
 * @package CIC\Cicbase\Traits
 */
trait FrontendInstantiating {
    protected static function initializeFrontend() {
        global $TYPO3_CONF_VARS;
        if (!$GLOBALS['TSFE']) {
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
                $TYPO3_CONF_VARS,
                GeneralUtility::_GP('id')
            );
        }

        if (!static::userSessionExists()) {
            $GLOBALS['TSFE']->initFEuser();
        }

        if (!is_object($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = static::initSysPage();
        }
        if (!is_object($GLOBALS['TSFE']->tmpl)) {
            /** @var TypoScriptFrontendController $tsfe */
            $tsfe = $GLOBALS['TSFE'];
            $tsfe->initTemplate();
        }
        /**
         * This is needed if we're trying to use RealURL :(
         */
        if (!is_array($GLOBALS['TSFE']->config)) {
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

