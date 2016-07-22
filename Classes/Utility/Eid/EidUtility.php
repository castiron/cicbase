<?php

namespace CIC\Cicbase\Utility\Eid;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EidUtility
 * @package CIC\Cicbase\Utility\Eid
 */
class EidUtility extends \TYPO3\CMS\Frontend\Utility\EidUtility {
    /**
     *
     */
    public static function initUserSession() {
        if(!$GLOBALS['TSFE']) {
            global $TYPO3_CONF_VARS;

            // Make new instance of TSFE object for initializing user:
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $TYPO3_CONF_VARS, 0, 0);
            $GLOBALS['TSFE']->connectToDB();

            // Initialize FE user:
            $GLOBALS['TSFE']->initFEuser();

            // Get typoscript
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->getCompressedTCarray();
        }
    }

    /**
     * @return bool
     */
    public static function userSessionExists() {
        return $GLOBALS['TSFE'] && $GLOBALS['TSFE']->fe_user && $GLOBALS['TSFE']->fe_user->user['uid'];
    }
}
