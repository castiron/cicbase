<?php namespace CIC\Cicbase\Service;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use CIC\Cicbase\Traits\FrontendInstantiating;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class TypolinkService
 * @package CIC\Cicbase\Service
 */
class TypolinkService implements SingletonInterface {
    use ExtbaseInstantiable;
    use FrontendInstantiating;

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
        /**
         * Do we have a TSFE?
         */
        if (TYPO3_MODE !== 'FE') {
            static::initializeFrontend();
        }

        /**
         * Do we have a cObj?
         */
        if (!is_object($this->contentObjectRenderer)) {
            $this->initContentObjectRenderer();
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

}
