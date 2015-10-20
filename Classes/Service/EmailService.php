<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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


class Tx_Cicbase_Service_EmailService implements Tx_Cicbase_Service_EmailServiceInterface {


	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 *
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * inject the objectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager objectManager
	 * @inject
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}


	/**
	 * Converts a string like:
	 *   Lisa Simpson : lisa@school.edu , Bart Simpson : bart@school.edu
	 *
	 * Into an array like:
	 *   [lisa@school.edu => "Lisa Simpson", bart@school.edu => "Bart Simpson"]
	 *
	 *
	 * The string format is useful when specifying multiple emails in typoscript constants.
	 *
	 * @param string $emailString
	 * @return array
	 */
	public static function parseCommonEmailFormat($emailString) {
		$emails = array();
		$peeps = t3lib_div::trimExplode(',', $emailString);
		if (count($peeps)) {
			foreach ($peeps as $peep) {
				$parts = t3lib_div::trimExplode(':', $peep);
				if (count($parts) == 2 && t3lib_div::validEmail($parts[1])) {
					$emails[$parts[1]] = $parts[0];
				}
			}
		}
		return $emails;
	}

	/**
	 * @param array|string $recipient recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
	 * @param array|string $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
	 * @param string $subject subject of the email
	 * @param string $templateName template name (UpperCamelCase)
	 * @param array $templateVariables variables to be passed to the Fluid view
	 * @param array $attachments An array of Swift_Attachment instances
	 * @return boolean TRUE on success, otherwise false
	 */
	public function sendTemplateEmail($recipient, $sender, $subject, $templateName, array $templateVariables = null, array $attachments = null) {

		$recipient = $this->cleanEmails($recipient);
		$sender = $this->cleanEmails($sender);


		$logInfo = json_encode(array(
			'sender' => $sender,
			'subject' => $templateName,
			'recipient' => $recipient,
		));

		if (empty($recipient)) {
			return self::log(__LINE__, "Can't send email. No recipients. $logInfo", FALSE);
		}



		$emailView = $this->objectManager->create('Tx_Fluid_View_StandaloneView');
		$emailView->setFormat('html');
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = t3lib_div::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . '/Email/' . $templateName;
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		if($templateVariables)
			$emailView->assignMultiple($templateVariables);
		$emailBody = $emailView->render();

		$message = t3lib_div::makeInstance('t3lib_mail_Message');
		$message->setTo($recipient)
			->setFrom($sender)
			->setSubject($subject);

		if ($attachments) {
			foreach($attachments as $att) {
				$message->attach($att);
			}
		}

		// Plain text example
		#$message->setBody($emailBody, 'text/plain');

		// HTML Email
		$message->setBody($emailBody, 'text/html');

		$message->send();

		if($message->isSent()) {
			self::log(__LINE__, "Email sent. $logInfo");
			return true;
		}

		self::log(__LINE__, "Email failed to send. $logInfo");
		return false;
	}

	protected function cleanEmails($emails) {
		if (is_array($emails)) return $emails;
		if (is_string($emails)) return self::parseCommonEmailFormat($emails);
		return array();
	}


	/**
	 * @param int $line
	 * @param string $msg
	 * @param bool $returnVal
	 * @return bool
	 */
	protected static function log($line, $msg, $returnVal = TRUE) {
		return Tx_Cicbase_Service_LoggerService::log(
			'cicbase',
			$line,
			"Email Service: $msg",
			$returnVal
		);
	}
}


?>