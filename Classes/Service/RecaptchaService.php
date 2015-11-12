<?php
namespace CIC\Cicbase\Service;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class RecaptchaService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * RecaptchaService constructor
	 * @param ConfigurationManagerInterface $configurationManager
	 */
	public function __construct(ConfigurationManagerInterface $configurationManager) {
		$this->settings = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
			'cicbase',
			'default'
		);
		if (is_array($this->settings['recaptcha'])) {
			if (array_key_exists('public_key', $this->settings['recaptcha']) &&
				array_key_exists('private_key', $this->settings['recaptcha'])) {
				$this->enabled = true;
			}
		}
	}

	/**
	 * Adds a recaptcha argument to the given set of controller arguments
	 * @param Arguments $controllerArguments
	 */
	public function addRecaptchaValidation(Arguments $controllerArguments) {
		$controllerArguments->addNewArgument('recaptcha');
		$recaptchaArg = $controllerArguments->getArgument('recaptcha');

		// First, set a converter
		$stringConverter = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\StringConverter');
		$mapping = $recaptchaArg->getPropertyMappingConfiguration();
		$mapping->setTypeConverter($stringConverter);

		// Now add non-empty and recaptcha validation
		$validatorResolver = GeneralUtility::makeInstance('CIC\\Cicbase\\Validation\\ValidatorResolver');

		$conjunctionValidator = $validatorResolver->createValidator('Conjunction');
		$conjunctionValidator->addValidator($validatorResolver->createValidator('NotEmpty'));
		$conjunctionValidator->addValidator(
			$validatorResolver->createValidator('CIC\\Cicbase\\Validation\\Validator\\RecaptchaValidator')
		);

		$recaptchaArg->setValidator($conjunctionValidator);
		$recaptchaArg->setValue(GeneralUtility::_POST('g-recaptcha-response'));
	}

	/**
	 * @return boolean
	 */
	public function recaptchaEnabled() {
		return $this->enabled;
	}

	/**
	 * @param string $value The POSTed response from the ReCAPTCHA widget
	 * @return bool
	 */
	public function validateRecaptcha($value) {
		$request = array(
			'secret' => $this->settings['recaptcha']['private_key'],
			'response' => $value,
			'remoteip' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
		);

		if (!empty($request['response'])) {
			$response = $this->queryVerificationServer($request);
			if (!$response) {
				$result['error'] = 'Recaptcha verification server did not respond';
			}
			return $response->success;
		}
		return false;
	}

	/**
	 * Query reCAPTCHA server for captcha-verification
	 *
	 * @param array $data
	 * @return array Array with verified- (boolean) and error-code (string)
	 */
	protected function queryVerificationServer($data)
	{
		$endpoint = "https://www.google.com/recaptcha/api/siteverify";
		$request = GeneralUtility::implodeArrayForUrl('', $data);
		$response = GeneralUtility::getURL($endpoint . '?' . $request);

		return json_decode($response);
	}
}

