<?php
namespace CIC\Cicbase\Scheduler;

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Claus Due, Wildside A/S <claus@wildside.dk>
*  Incorporated into cicbase, by Michael McManus, Cast Iron Coding, <michael@castironcoding.com>
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
 * Scheduler task to execute CommandController commands
 *
 * @package cicbase
 * @subpackage Scheduler
 */
class Task extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/**
	 * @var string
	 */
	protected $commandIdentifier;

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * @var array
	 */
	protected $defaults;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Function execute from the Scheduler
	 *
	 * @return boolean TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		list ($extensionName, $controllerName, $commandName) = explode(':', $this->commandIdentifier);
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->injectObjectManager($objectManager);
		$request = $this->objectManager->create('TYPO3\CMS\Extbase\Mvc\Cli\Request');
		$dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
		$response = $this->objectManager->create('TYPO3\CMS\Extbase\Mvc\Cli\Response');
		try {
			$upperCamelCaseExtensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extensionName);
			$upperCamelCaseControllerName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($controllerName);
			// TODO build class name a different way now that we have namespaces
			// see https://www.pivotaltracker.com/story/show/73980994
			$controllerObjectName = sprintf('Tx_%s_Command_%sCommandController', $upperCamelCaseExtensionName, $upperCamelCaseControllerName);
			$request->setControllerCommandName($commandName);
			$request->setControllerObjectName($controllerObjectName);
			$request->setArguments((array) $this->arguments);
			$dispatcher->dispatch($request, $response);
			return TRUE;
		} catch (\Exception $e) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog($e->getMessage(), $extensionName, $e->getCode());
			return FALSE;
		}
	}

	/**
	 * @param string $commandIdentifier
	 */
	public function setCommandIdentifier($commandIdentifier) {
		$this->commandIdentifier = $commandIdentifier;
	}

	/**
	 * @return string
	 */
	public function getCommandIdentifier() {
		return $this->commandIdentifier;
	}

	/**
	 * @param array $arguments
	 */
	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * @param array $defaults
	 */
	public function setDefaults($defaults) {
		$this->defaults = $defaults;
	}

	/**
	 * @return array
	 */
	public function getDefaults() {
		return $this->defaults;
	}

	/**
	 * @param string $argumentName
	 * @param mixed $argumentValue
	 */
	public function addDefaultValue($argumentName, $argumentValue) {
		if (is_bool($argumentValue)) {
			$argumentValue = intval($argumentValue);
		}
		$this->defaults[$argumentName] = $argumentValue;
	}

	/**
	 * Return a text representation of the selected command and arguments
	 *
	 * @return string
	 */
	public function getAdditionalInformation() {
		$label = $this->commandIdentifier;
		if (count($this->arguments) > 0) {
			$arguments = array();
			foreach ($this->arguments as $argumentName=>$argumentValue) {
				if ($argumentValue != $this->defaults[$argumentName]) {
					array_push($arguments, $argumentName . '=' . $argumentValue);
				}
			}
			$label .= ' ' . implode(', ', $arguments);
		}
		return $label;
	}

}

?>