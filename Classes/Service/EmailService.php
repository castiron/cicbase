<?php
namespace CIC\Cicbase\Service;
use CIC\Cicbase\Utility\Arr;
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
 *   whitelist = teaching.bites@springfield.edu : Edna Krabappel , school.rocks@springfield.edu : Lisa Simpson
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
 *
 *       # Prevent BE editors from overriding this template in the BE Email Templates Module (default: FALSE)
 *       # optional
 *       noOverride = 1
 *
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
	 * plugin.tx_ext.settings.email.whitelist = teaching.bites@springfield.edu : Edna Krabappel , school.rocks@springfield.edu : Lisa Simpson
	 *
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
	 * Our own storage of fleshed out template paths
	 *
	 * @var array
	 */
	protected $foundTemplatePaths = array();

	/**
	 * Our own storage of templates that correspond to a
	 * database record to use instead of the template file.
	 *
	 * @var array
	 */
	protected $foundTemplateOverrides = array();

	/**
	 * @var \CIC\Cicbase\Domain\Repository\EmailTemplateRepository
	 * @inject
	 */
	protected $emailTemplateRepository;


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
			$whitelist = GeneralUtility::trimExplode(',', $this->settings['whitelist']);
			foreach($whitelist as $whitelistConf) {
				$parts = GeneralUtility::trimExplode(':', $whitelistConf);
				if (count($parts) != 2) continue;
				$this->addToWhitelist($parts[1], $parts[0]);
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
	 * This is a helper method for other classes, not necessarily part of the EmailService API.
	 *
	 * Gets the raw file string for a template key "{ext}.{templateKey}"
	 *
	 * @see getAvailableTemplateKeys()
	 *
	 * @param string $templateKey
	 * @return string
	 */
	public function getTemplateBodyFromKey($templateKey) {
		$parts = explode('.', $templateKey);
		$ext = $parts[0];
		$key = $parts[1];

		$extbaseFrameworkConfiguration = $this->getTyposcriptForExtension($ext);
		$rootPaths = self::grabRootPathsFromExtConf($extbaseFrameworkConfiguration);

		if (!count($rootPaths)) return FALSE;

		$extSettings = $extbaseFrameworkConfiguration['settings'];
		if (!isset($extSettings['email']['templates'][$key])) return FALSE;

		$templateDefinition = $extSettings['email']['templates'][$key];

		$file = self::findRealTemplateFile($rootPaths, $templateDefinition);
		if (!$file) return FALSE;

		return file_get_contents($file);
	}

	/**
	 * This is a helper method for other classes, not necessarily part of the EmailService API.
	 *
	 * @return array
	 */
	public function getAvailableTemplateKeys() {
		$exts = array();
		$keys = array();

		$cicbaseSettings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'cicbase', 'default'
		);

		if (isset($cicbaseSettings['emailTemplateOverrides']['extensions'])) {
			$exts = $cicbaseSettings['emailTemplateOverrides']['extensions'];
			if (!is_array($exts) || !count($exts)) {
				return array();
			}
		}

		$whereAllowed = function ($templateDefinition, $templateKey) {
			return !isset($templateDefinition['noOverride']) || !$templateDefinition['noOverride'];
		};
		$keyTrimmer = function ($key) { return rtrim($key, '.'); };
		foreach ($exts as $extKey) {
			$extConf = $this->getTyposcriptForExtension($extKey);
			if (!is_array($extConf)) continue;

			$extSettings = $extConf["settings"];
			if (!is_array($extSettings)) continue;
			Arr::walkKeysRecursive($extSettings, $keyTrimmer);

			if (!isset($extSettings['email']['templates'])) continue;
			$templates = $extSettings['email']['templates'];

			$allowedTemplates = Arr::spliceWhere($templates, $whereAllowed);

			$keys[$extKey] = array_keys($allowedTemplates);
		}

		$this->emailTemplateRepository->filterOutExistingTemplateKeys($keys);
		return $keys;

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
		if(!$this->templateExists($templateName)) return '';

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
		$emailView->setFormat('html');


		if (isset($this->foundTemplateOverrides[$templateName])) {
			$emailView->setTemplateSource($this->foundTemplateOverrides[$templateName]);
		} else {
			$templatePathAndFilename = $this->getTemplatePath($templateName);
			$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		}
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
		return isset($this->foundTemplatePaths[$templateName]) ? $this->foundTemplatePaths[$templateName] : '';
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
		if (!isset($this->templates[$templateName])) return FALSE;
		if (!isset($this->templates[$templateName]['templateFile'])) return FALSE;
		if (isset($this->foundTemplateOverrides[$templateName])) return TRUE;
		if (isset($this->foundTemplatePaths[$templateName])) return TRUE;

		$framework = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$ext = strtolower($framework['extensionName']);
		/** @var \CIC\Cicbase\Domain\Model\EmailTemplate $record */
		$record = $this->emailTemplateRepository->findOneByTemplateKey("$ext.$templateName");

		if ($record) {
			$this->foundTemplateOverrides[$templateName] = $record->getBody();
			return TRUE;
		}

		$rootPaths = $this->getTemplateRootPaths();

		if (!count($rootPaths)) return FALSE;

		$file = self::findRealTemplateFile($rootPaths, $this->templates[$templateName]);
		if ($file) {
			$this->foundTemplatePaths[$templateName] = $file;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * This function mimics the way extbase controllers find view templates.
	 *
	 * @return array
	 */
	protected function getTemplateRootPaths() {
		return self::grabRootPathsFromExtConf($this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		));
	}

	/**
	 * @param array $rootPaths
	 * @param array $templateDefinition
	 * @return bool|string
	 */
	protected static function findRealTemplateFile(array $rootPaths, array $templateDefinition) {
		$templateFile = $templateDefinition['templateFile'];
		foreach ($rootPaths as $possiblePath) {
			$rootPath = GeneralUtility::getFileAbsFileName($possiblePath);
			$file = rtrim($rootPath, '/') . '/' . ltrim($templateFile, '/');
			if (is_file($file)) {
				return $file;
			}
		}
		return FALSE;
	}

	/**
	 * @param array $extbaseFrameworkConfiguration
	 * @return array
	 */
	protected static function grabRootPathsFromExtConf(array $extbaseFrameworkConfiguration) {
		$rootPaths = array();
		if (
			!empty($extbaseFrameworkConfiguration['view']['templateRootPaths'])
			&& is_array($extbaseFrameworkConfiguration['view']['templateRootPaths'])
		) {
			$rootPaths = \TYPO3\CMS\Extbase\Utility\ArrayUtility::sortArrayWithIntegerKeys($extbaseFrameworkConfiguration['view']['templateRootPaths']);
			$rootPaths = array_reverse($rootPaths, TRUE);
		}

		// @todo remove handling of deprecatedSetting two versions after 6.2
		if (
			isset($extbaseFrameworkConfiguration['view']['templateRootPath'])
			&& strlen($extbaseFrameworkConfiguration['view']['templateRootPath']) > 0
		) {
			$rootPaths[] = $extbaseFrameworkConfiguration['view']['templateRootPath'];
		}
		return $rootPaths;
	}

	/**
	 * @param $ext
	 * @return array
	 */
	protected function getTyposcriptForExtension($ext) {
		$allTyposcript = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);

		if (!isset($allTyposcript['plugin.']["tx_$ext."])) return FALSE;
		$keyTrimmer = function ($key) { return rtrim($key, '.'); };
		$extConf = $allTyposcript['plugin.']["tx_$ext."];
		Arr::walkKeysRecursive($extConf, $keyTrimmer);
		return $extConf;
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
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
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


		return $message->isSent();
	}
}


?>