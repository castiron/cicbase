<?php namespace CIC\Cicbase\Service;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class TypolinkService
 * @package CIC\Cicbase\Service
 */
class TypolinkService implements SingletonInterface {
    use ExtbaseInstantiable;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    var $contentObjectRenderer;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    var $objectManager;

    /**
     *
     */
    public function initializeObject() {
        if (!is_object($GLOBALS['TSFE'])) {
            throw new \RuntimeException(__CLASS__ . " doesn't currently work without an instantiated TSFE.");
        }
        if (!is_object($this->contentObjectRenderer)) {
            $this->initContentObjectRenderer();
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
     * @param string|int|array $config
     * @return string
     */
    public function typolinkUrl($config) {
        if (!is_array($config)) {
            $config = array('parameter' => $config);
        }
        return $this->contentObjectRenderer->typoLink_URL($config);
    }

    /**
     *
     */
    protected function initContentObjectRenderer() {
        $this->contentObjectRenderer = $this->objectManager->get('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
    }

    /**
     * @return PageRepository
     */
    protected function initSysPage() {
        /** @var PageRepository $out */
        $out = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $out->init(false);
        return $out;
    }


}
