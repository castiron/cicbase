<?php
namespace CIC\Cicbase\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 *
 * @package orbest
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 */
	private $currentUser = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * Gets the current logged in user, or redirects user to login page.
	 *
	 * @return null|\CIC\Cicregister\Domain\Model\FrontendUser
	 */
	public function getCurrentUserOrRedirect() {
		$user = $this->getCurrentUser();
		if ($user) {
			return $user;
		}

		$pid = $this->settings['pids']['register']['login'];
		$env = GeneralUtility::getIndpEnv('_ARRAY');
		$here = $env['REQUEST_URI'];
		$args = array(
			'return_url' => $here
		);
		$returnMsgKey = $this->getReturnMessageKey();
		if ($returnMsgKey !== '') {
			$args = array_merge($args, array('return_msg' => $returnMsgKey));
		}

		$uriBuilder = $this->uriBuilder
			->reset()
			->setCreateAbsoluteUri(true)
			->setTargetPageUid($pid)
			->setArguments($args);
		$uri = $uriBuilder->build();
		header('Location: ' . $uri);
		die();
		return null;
	}

	/**
	 * Gets the current logged in user, if available.
	 *
	 * @return \CIC\Cicregister\Domain\Model\FrontendUser
	 */
	public function getCurrentUser() {
		if (!$this->currentUser) {
			if (isset($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->fe_user)) {
				$uid = $GLOBALS['TSFE']->fe_user->user['uid'];
				if ($uid) {

					// For some reason, this is not injected every time
					if (!$this->frontendUserRepository) {
						$this->frontendUserRepository = $this->objectManager->get('TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository');
					}

					$query = $this->frontendUserRepository->createQuery();
					$qSettings = $query->getQuerySettings();
					$qSettings->setRespectStoragePage(FALSE);
					$query->setQuerySettings($qSettings);
					$result = $query->matching($query->equals('uid', $uid))->execute();
					$this->currentUser = $result->getFirst();
				}
			}
		}
		return $this->currentUser;
	}

	/**
	 * Returns true if the user is logged in
	 *
	 * @return bool
	 */
	public function userIsLoggedIn() {
		if ($this->currentUser) return TRUE;
		return isset($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->fe_user) && isset($GLOBALS['TSFE']->fe_user->user['uid']);
	}

	/**
	 * Override this to set a specific message when the user is
	 * redirected to the login form.
	 *
	 * @return string
	 */
	public function getReturnMessageKey() {
		return 'accessDenied';
	}

	/**
	 * Gets a list of allowed controller actions.
	 * Fails if there's no request or there's no controller configuration.
	 *
	 * @return null|array
	 */
	protected function getAllowedControllerActions() {
		$framework = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		if ($this->request) {
			$controller = $this->request->getControllerName();
			if (isset($framework['controllerConfiguration'][$controller]['actions'])) {
				return $framework['controllerConfiguration'][$controller]['actions'];
			}
		}

		return NULL;
	}


	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
	 */
	protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
		$this->view->assign('frontendUser', $this->currentUser);
	}

	/**
	 * Looks for form error message in localization file.
	 *
	 * First tries 'form-errors-{controller}-{action}', then
	 * tries 'form-errors-{controller}' for translation.
	 *
	 * {controller} and {action} are lowercase
	 *
	 * @return NULL|string
	 */
	protected function getErrorFlashMessage() {
		$action = strtolower($this->request->getControllerActionName());
		$controller = strtolower($this->request->getControllerName());
		$ext = $this->request->getControllerExtensionName();

		$message = LocalizationUtility::translate("form-errors-$controller-$action", $ext);
		if ($message) {
			return $message;
		}

		$message = LocalizationUtility::translate("form-errors-$controller", $ext);
		if ($message) {
			return $message;
		}

		return parent::getErrorFlashMessage();
	}

}

?>