<?php

namespace CIC\Cicbase\ViewHelpers\Fal;

/**
 * Class CIC\Cicbase\ViewHelpers\Fal\ImageViewHelper
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper {
	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 *
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
	}

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @return string
     */
	public function render() {
	    $out = parent::render();
        if ($this->urlOnly() && preg_match('/src="([^"]*)"/', $out, $matches)) {
            $out = $matches[1];
        }
        return $out;
	}

    /**
     * @return bool
     */
	protected function urlOnly() {
	    return intval($this->arguments['width']) ? true : false;
    }
}
