<?php
namespace CIC\Cicbase\ViewHelpers\Link;


class FileViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
		$this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document');
		$this->registerTagAttribute('rev', 'string', 'Specifies the relationship between the linked document and the current document');
		$this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
	}

	/**
	 * @param string $uri the URI that will be put in the href attribute of the rendered link tag
	 * @param string $defaultScheme scheme the href attribute will be prefixed with if specified $uri does not contain a scheme already
	 * @param boolean $absolute
	 * @return string Rendered link
	 * @api
	 */
	public function render($uri, $defaultScheme = 'http', $absolute = TRUE) {

		$uri = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($uri);
		$uri = substr($uri, strlen(PATH_site));
		if (TYPO3_MODE === 'BE' && $absolute === FALSE) {
			$uri = '../' . $uri;
		}
		if ($absolute === TRUE) {
			$uri = $this->controllerContext->getRequest()->getBaseURI() . $uri;
		}

		$scheme = parse_url($uri, PHP_URL_SCHEME);
		if ($scheme === NULL && $defaultScheme !== '') {
			$uri = $defaultScheme . '://' . $uri;
		}

		$this->tag->addAttribute('href', $uri);
		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);

		return $this->tag->render();
	}
}

?>