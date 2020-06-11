<?php

namespace CIC\Cicbase\ViewHelpers\Link;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;


/**
 * Class GoogleMapsViewHelper
 * @package CIC\Cicbase\ViewHelpers\Link
 *
 * Generates a link to a location on google maps given an address/place/venue
 *
 * ```
 * <c:link.googleMaps address="{myObj.address}" class="add-map-marker-icon">
 *   Find address on google maps!
 * </c:link.googleMaps>
 * ```
 *
 * Always opens with `target="_blank"`. Empty addresses or addresses that fail
 * any processing will result in an `a` tag with `href` attribute. So markup
 * stays the same, but it's unclickable.
 *
 */
class GoogleMapsViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('address', 'string', 'The address to render a map for', true);
		$this->registerUniversalTagAttributes();
	}

	/**
	 * @param string $address
	 * @return string
	 */
	public function render($address = NULL) {
		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);

		if ($this->addressIsInvalid($address)) {
			return $this->tag->render();
		}

		$place = urlencode(preg_replace('/\s+/', ' ', $address));

		$this->tag->addAttributes(array(
			'href' => "https://www.google.com/maps/place/$place",
			'target' => '_blank'
		), FALSE);


		return $this->tag->render();
	}


	/**
	 * @param null $address
	 * @return bool
	 */
	public function addressIsInvalid($address = NULL) {
		if (!$address || !trim($address)) {
			return TRUE;
		}

		// TODO more processing here?
		return FALSE;
	}


}
