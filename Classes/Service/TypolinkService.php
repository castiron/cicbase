<?php namespace CIC\Cicbase\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class TypolinkService
 * @package CIC\Cicbase\Service
 */
class TypolinkService implements SingletonInterface {
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
        if (!is_object($this->contentObjectRenderer)) {
            $this->initContentObjectRenderer();
        }
        if (!is_object($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = static::initSysPage();
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
        $out = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $out->init();
        return $out;
    }

    /**
     *
     */
    public static function instance() {
        /** @var ObjectManager $objManager */
        $objManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        return $objManager->get('CIC\\Cicbase\\Service\\TypolinkService');
    }
}
