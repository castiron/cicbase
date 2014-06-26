<?php
namespace CIC\Cicbase\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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


/**
 * This service makes things a little easier to
 * send emails. It uses the new T3 6+ Mail API.
 *
 * EXAMPLE:
 * plugin.tx_ext.settings.email {
 *
 *   # Whitelist useful for debugging.
 *   whitelist {
 *     0 {
 *       name = Peter Peter
 *       email = peter@castironcoding.com
 *     }
 *   }
 *
 *   # If there's a whitelist, then you can
 *   # specify that it always be used as the
 *   # email recipients. Otherwise, if emails
 *   # don't match the whitelist, no email would
 *   # be sent.
 *   alwaysOverrideWithWhitelist = 1
 *
 *
 *   # Specify all templates here
 *   templates {
 *
 *     # Template name. This is how you specify which template to use
 *     partnerCreated {
 *
 *       # mandatory
 *       subject = A new partner has been created and needs approval.
 *
 *       # mandatory
 *       templateFile = Email/PartnerCreated.html
 *
 *       # optional
 *       sender {
 *         name = Someone Last
 *         email = email@test.com
 *       }
 *     }
 *   }
 *
 *   # If not otherwise specified, use this
 *   # sender address.
 *   # This can also be specified with:
 *   #   $TYPO3_CONF_VARS['MAIL']['defaultMailFromAddress']
 *   #   $TYPO3_CONF_VARS['MAIL']['defaultMailFromName']
 *   defaultSender {
 *     name = Oregon Best
 *     email = info@oregonbest.org
 *   }
 * }
 *
 * // Create a basic email
 * $msg = $this->emailService->createMessage('partnerCreated', array('email@test.com' => 'Email Test'));
 *
 * // Add an attachment
 * $attachment = $this->emailService->createAttachment('some/path.jpg', 'image/jpeg');
 * $msg->attach($attachment);
 *
 * // Send the message
 * $msg->send();
 *
 * Class EmailService
 * @package CIC\Cicbase\Service
 */
class EmailService implements \TYPO3\CMS\Core\SingletonInterface {


	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * A list of the only addresses this service will
	 * send emails to. Can be set up in typoscript:
	 *
	 * plugin.tx_ext.settings.email.whitelist {
	 *   0 {
	 *     name = Edna Krabappel
	 *     email = teaching.bites@springfield.edu
	 *   }
	 * }
	 *
	 * @var array
	 */
	protected $whitelist = array();

	/**
	 * Instead of just checking that emails are listed in the
	 * whitelist, replace all emails with the whitelist no
	 * matter what. This can be set in typoscript:
	 *
	 * plugin.tx_ext.settings.email.alwaysOverrideWithWhitelist = 1
	 *
	 * @var bool
	 */
	protected $alwaysOverrideWithWhitelist = FALSE;

	/**
	 * If no sender is provided in template settings or when creating
	 * the mail message, then this value will be used. This can be
	 * set in typoscript:
	 *
	 * plugin.tx_ext.settings.email.defaultSender {
	 *   name = My Website
	 *   email = my@website.com
	 * }
	 *
	 *
	 * @var array
	 */
	protected $defaultSender = array();

	/**
	 * A list of template configurations as
	 * specified in typoscript. Should be something like
	 * plugin.tx_ext.settings.email.templates {
	 *   thankYou {
	 *     template = Email\ThankYou.html
	 *     subject = Thank you for registering.
	 *   }
	 *   contact {
	 *     templateFile = Email\Contact.html
	 *     subject = Thank you for saying hello. We'll get back to you as soon as we can.
	 *
	 *     # A sender is optional in TS. It could be specified in multiple places.
	 *     sender {
	 *       name = Awesome Site Name
	 *       email = info@awesomesite.com
	 *     }
	 *   }
	 * }
	 *
	 * @var array
	 */
	protected $templates = array();


		/**
		 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
		 * @return void
		 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$allSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		if(isset($allSettings['email'])) {
			$this->settings = $allSettings['email'];
		}
	}

	/**
	 * Injects the object manager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Gets called after dependency injections.
	 */
	public function initializeObject() {
		// Build whitelist
		if(isset($this->settings['whitelist'])) {
			foreach($this->settings['whitelist'] as $email) {
				$this->addToWhitelist($email['name'], $email['email']);
			}
		}
		if(isset($this->settings['alwaysOverrideWithWhitelist'])) {
			$this->alwaysOverrideWithWhitelist = $this->settings['alwaysOverrideWithWhitelist'];
		}
		if(isset($this->settings['defaultSender'])) {
			$this->defaultSender = array($this->settings['defaultSender']['email'] => $this->settings['defaultSender']['name']);
		}
		if(isset($this->settings['templates'])) {
			foreach($this->settings['templates'] as $templateName => $templateConfig) {
				if(isset($templateConfig['templateFile']) && isset($templateConfig['subject'])) {
					$this->templates[$templateName] = $templateConfig;
				} else {
					throw new \Exception("All email templates need a 'subject' and a 'templateFile' field at a minimum. See CIC\\Cicbase\\Service\\EmailService for more details.");
				}
			}
		}
	}

	/**
	 * Creates a MailMessage object as preconfigured as possible.
	 * This means it will have body, subject, from, and to variables
	 * set already. Exceptions are thrown if we can't find some of these
	 * variables, which usually means there's an error with the typoscript.
	 *
	 * @param string $templateName      As written in typoscript. Must exist in typoscript.
	 * @param array $recipients         array(email => name, email => name)
	 * @param array $templateVariables  Variables to pass to the template view.
	 * @param array $sender             array(email => name, email => name)
	 * @throws \Exception
	 * @returns \TYPO3\CMS\Core\Mail\MailMessage
	 */
	public function createMessage($templateName, array $recipients, array $templateVariables = NULL, array $sender = NULL) {
		if(!$this->templateExists($templateName)) {
			throw new \Exception("You need to add $templateName name to the email templates in typoscript. See CIC\\Cicbase\\Service\\EmailService for more details.");
		}

		$recipients = $this->cleanRecipients($recipients);
		$sender = $this->cleanSender($templateName, $sender);
		if(!$sender) {
			throw new \Exception("Can't send an email without it being from someone. Please provide a sender. See CIC\\Cicbase\\Service\\EmailService for more details.");
		}

		$body = $this->buildMessageBody($templateName, $templateVariables);
		if($body == '') {
			throw new \Exception("Can't send an email without a body. Please check your typoscript and template path. See CIC\\Cicbase\\Service\\EmailService for more details.");
		}
		$subject = $this->getTemplateSubject($templateName);

		/** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
		$mail = GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
		$mail->setTo($recipients);
		$mail->setFrom($sender);
		$mail->setBody($body, 'text/html');
		$mail->setSubject($subject);

		return $mail;
	}

	/**
	 * Creates a Swift_Attachment. You can attach this
	 * to a MailMessage object with $msg->attach().
	 *
	 * @param string $path
	 * @param string $contentType
	 * @return \Swift_Mime_Attachment
	 */
	public function createAttachment($path, $contentType = NULL) {
		$attachment = \Swift_Attachment::fromPath($path, $contentType);
		return $attachment;
	}

	/**
	 * Checks whitelist settings to determine appropriate recipients
	 *
	 * @param array $recipients
	 * @return array
	 */
	protected function cleanRecipients(array $recipients) {
		if($this->hasWhitelist()) {
			if($this->alwaysOverrideWithWhitelist) {
				return $this->whitelist;
			} else {
				return array_intersect_assoc($this->whitelist, $recipients);
			}
		} else {
			return $recipients;
		}
	}

	/**
	 * @param string $templateName
	 * @param array $sender
	 * @return bool|array
	 */
	protected function cleanSender($templateName, array $sender = NULL) {
		if($sender) {
			return $sender;
		}
		if(isset($this->settings['templates'][$templateName]) && isset($this->settings['templates'][$templateName]['sender'])) {
			$senderInfo = $this->settings['templates'][$templateName]['sender'];
			return array($senderInfo['email'] => $senderInfo['name']);
		}
		if($this->hasDefaultSender()) {
			return $this->defaultSender;
		}
		$systemDefault = MailUtility::getSystemFrom();
		if($systemDefault) {
			return $systemDefault;
		}
		return FALSE;
	}

	/**
	 * Renders the message using the template specified in typoscript.
	 *
	 * @param $templateName
	 * @param array $templateVariables
	 * @return string
	 */
	protected function buildMessageBody($templateName, array $templateVariables = NULL) {
		if(!$this->templateExists($templateName)) {
			return '';
		}

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
		$emailView->setFormat('html');
		$templatePathAndFilename = $this->getTemplatePath($templateName);
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		if($templateVariables) {
			$emailView->assignMultiple($templateVariables);
		}
		return $emailView->render();
	}


	/**
	 * Finds template path using typoscript values.
	 *
	 * @param string $templateName
	 * @return string
	 */
	protected function getTemplatePath($templateName) {
		if($this->templateExists($templateName)) {
			$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
			$templateRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
			return $templateRootPath . $this->templates[$templateName]['templateFile'];
		}
		return '';
	}

	/**
	 * Finds subject in typoscript.
	 *
	 * TODO: Maybe allow some interpolation using the template variables.
	 *
	 * @param $templateName
	 * @return string
	 */
	protected function getTemplateSubject($templateName) {
		if($this->templateExists($templateName)) {
			return $this->templates[$templateName]['subject'];
		}
		return '';
	}

	/**
	 * @param string $templateName
	 * @return bool
	 */
	protected function templateExists($templateName) {
		return isset($this->templates[$templateName]);
	}


	/**
	 * @return bool
	 */
	public function hasWhitelist() {
		return (bool) count($this->whitelist);
	}

	/**
	 * @return bool
	 */
	public function hasDefaultSender() {
		return (bool) count($this->defaultSender);
	}

	/**
	 * Adds an email to the whitelist.
	 *
	 * @param string $name
	 * @param string $email
	 */
	public function addToWhitelist($name, $email) {
		$this->whitelist[$email] = $name;
	}

	/**
	 * @param array $whitelist
	 */
	public function setWhitelist($whitelist) {
		$this->whitelist = $whitelist;
	}

	/**
	 * @return array
	 */
	public function getWhitelist() {
		return $this->whitelist;
	}

	/**
	 * This method sends an email without using much of the typoscript configurations.
	 *
	 * It only checks the whitelist really and doesn't use template configurations in typoscript.
	 *
	 * @param array $recipients recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
	 * @param array $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
	 * @param string $subject subject of the email
	 * @param string $templateName template name (UpperCamelCase)
	 * @param array $templateVariables variables to be passed to the Fluid view
	 * @param array $attachments An array of Swift_Attachment instances
	 * @return boolean TRUE on success, otherwise false
	 * @deprecated For all new T3 6.x extensions, you should not use this method anymore.
	 */
	public function sendTemplateEmail(array $recipients, array $sender, $subject, $templateName, array $templateVariables = null, array $attachments = null) {

		$recipients = $this->cleanRecipients($recipients);
		$sender = $this->cleanSender($sender);

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
		$emailView->setFormat('html');
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . '/Email/' . $templateName;
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		if($templateVariables) {
			$emailView->assignMultiple($templateVariables);
		}
		$emailBody = $emailView->render();

		$message = GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
		$message->setTo($recipients)
			->setFrom($sender)
			->setSubject($subject);

		foreach($attachments as $att) {
			$message->attach($att);
		}

		// Plain text example
		#$message->setBody($emailBody, 'text/plain');

		// HTML Email
		$message->setBody($emailBody, 'text/html');

		$message->send();


		return $message->isSent();
	}
}


?>