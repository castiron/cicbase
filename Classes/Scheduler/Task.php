<?php
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
class Tx_Cicbase_Scheduler_Task extends Tx_Scheduler_Task {

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
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Function execute from the Scheduler
	 *
	 * @return boolean TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		list ($extensionName, $controllerName, $commandName) = explode(':', $this->commandIdentifier);
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->injectObjectManager($objectManager);
		$request = $this->objectManager->create('Tx_Extbase_MVC_CLI_Request');
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$response = $this->objectManager->create('Tx_Extbase_MVC_CLI_Response');
		try {
			$upperCamelCaseExtensionName = t3lib_div::underscoredToUpperCamelCase($extensionName);
			$upperCamelCaseControllerName = t3lib_div::underscoredToUpperCamelCase($controllerName);
			$controllerObjectName = sprintf('Tx_%s_Command_%sCommandController', $upperCamelCaseExtensionName, $upperCamelCaseControllerName);
			$request->setControllerCommandName($commandName);
			$request->setControllerObjectName($controllerObjectName);
			$request->setArguments((array) $this->arguments);
			$dispatcher->dispatch($request, $response);
			return TRUE;
		} catch (Exception $e) {
			t3lib_div::sysLog($e->getMessage(), $extensionName, $e->getCode());
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