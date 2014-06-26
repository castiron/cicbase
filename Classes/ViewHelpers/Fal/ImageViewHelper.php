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
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 *
	 * @param integer $fileUid
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
	 * @param boolean $urlOnly Just return the URL of the file
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string rendered tag.
	 */
	public function render($fileUid = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE, $urlOnly = FALSE) {
		$out = '';
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$file = $fileRepository->findByUid($fileUid);
		try {
			if($file) {
				$out = parent::render($file->getCombinedIdentifier(), $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight, $treatIdAsReference);
			}
			if ($urlOnly && preg_match('/src="([^"]*)"/', $out, $matches)) {
				$out = $matches[1];
			}
		} catch (\Exception $e) {
			$out = null;
		}
		return $out;
	}
}

?>