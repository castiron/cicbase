<?php

namespace CIC\Cicbase\ViewHelpers;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Cast Iron Coding
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Set up a ReCAPTCHA field in a form - includes required javascript and div element only
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class RecaptchaViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var bool
	 */
	protected $escapeOutput = false;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Returns a raw value
	 *
	 * @return String value
	 */
	public function render() {
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'cicbase', 'default');
		$pubkey = $this->settings['recaptcha']['public_key'];
		if (empty($pubkey)) return '';

		$out = "<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>\n";
		$out .= "<div class=\"g-recaptcha\" data-sitekey=\"$pubkey\"></div>";
		return $out;
	}
}

?>
