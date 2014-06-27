<?php
namespace CIC\Cicbase\Scheduler;
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Claus Due, Wildside A/S <claus@wildside.dk>
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
 * Field provider for Extbase CommandController Scheduler task
 *
 * @package Cicbase
 * @subpackage Scheduler
 */
class FieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Cli\CommandManager
	 */
	protected $commandManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \CIC\Cicbase\Scheduler\Task
	 */
	protected $task;

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\Cli\CommandManager $commandManager
	 */
	public function injectCommandManager(\TYPO3\CMS\Extbase\Mvc\Cli\CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->injectObjectManager($objectManager);
		$commandManager = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Cli\CommandManager');
		$this->injectCommandManager($commandManager);
		$reflectionService = $this->objectManager->get('TYPO3\CMS\Extbase\Reflection\ReflectionService');
		$this->injectReflectionService($reflectionService);
	}

	/**
	 * Render additional information fields within the scheduler backend.
	 *
	 * @param array $taskInfo Array information of task to return
	 * @param mixed $task \CIC\Cicbase\Scheduler\Task or tx_scheduler_Execution instance
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the calling object (BE module of the Scheduler)
	 * @return array Additional fields
	 * @see interfaces/tx_scheduler_AdditionalFieldProvider#getAdditionalFields($taskInfo, $task, $schedulerModule)
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		$this->task = $task;
		if ($this->task) {
			$this->task->setScheduler();
		}
		$fields = array();
		$fields['action'] = $this->getCommandControllerActionField();
		if ($this->task !== NULL && $this->task->getCommandIdentifier()) {
			$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
			$fields['description'] = $this->getCommandControllerActionDescriptionField();
			$argumentFields = $this->getCommandControllerActionArgumentFields($command->getArgumentDefinitions());
			$fields = array_merge($fields, $argumentFields);
			$this->task->save();
		}
		return $fields;
	}

	/**
	 * Validates additional selected fields
	 *
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
	 * @return boolean
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		return TRUE;
	}

	/**
	 * Saves additional field values
	 *
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
	 * @return boolean
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->setCommandIdentifier($submittedData['task_cicbase']['action']);
		$task->setArguments($submittedData['task_cicbase']['arguments']);
		return TRUE;
	}

	/**
	 * Get description of selected command
	 *
	 * @return string
	 */
	protected function getCommandControllerActionDescriptionField() {
		$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
		return array(
			'code' => '',
			'label' => '<strong>' . htmlspecialchars($command->getDescription()) . '</strong>'
		);
	}

	/**
	 * Gets a select field containing all possible CommandController actions
	 *
	 * @return array
	 */
	protected function getCommandControllerActionField() {
		$commands = $this->commandManager->getAvailableCommands();
		$options = array();
		foreach ($commands as $command) {
			if ($command->isInternal() === FALSE) {
				$classNameParts = explode('_', $command->getControllerClassName());
				$identifier = $command->getCommandIdentifier();
				$options[$identifier] = $classNameParts[1] . ' ' . str_replace('CommandController', '', $classNameParts[3]) . ': ' . $command->getControllerCommandName();
			}
		}
		$name = "action";
		$currentlySelectedCommand = $this->task !== NULL ? $this->task->getCommandIdentifier() : NULL;
		return array(
			'code' => $this->renderSelectField($name, $options, $currentlySelectedCommand),
			'label' => $this->getActionLabel()
		);
	}

	/**
	 * Gets a set of fields covering arguments which must be sent to $currentControllerAction.
	 * Also registers the default values of those fields with the Task, allowing
	 * them to be read upon execution.
	 *
	 * @param array $argumentDefinitions
	 * @return array
	 */
	protected function getCommandControllerActionArgumentFields(array $argumentDefinitions) {
		$fields = array();
		$argumentValues = $this->task->getArguments();
		foreach ($argumentDefinitions as $index=>$argument) {
			$name = $argument->getName();
			$defaultValue = $this->getDefaultArgumentValue($argument);
			$this->task->addDefaultValue($name, $defaultValue);
			$value = isset($argumentValues[$name]) ? $argumentValues[$name] : $defaultValue;
			$fields[$name] = array(
				'code' => $this->renderField($argument, $value),
				'label' => $this->getArgumentLabel($argument)
			);
		}
		return $fields;
	}

	/**
	 * Gets a label for $key based on either provided extension or currently
	 * selected CommandController extension,´
	 *
	 * @param string $localLanguageKey
	 * @param string $extensionName
	 * @return string
	 */
	protected function getLanguageLabel($localLanguageKey, $extensionName = NULL) {
		if (!$extensionName) {
			list ($extensionName, $commandControllerName, $commandName) = explode(':', $this->task->getCommandIdentifier());
		}
		$label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localLanguageKey, $extensionName);
		return $label;
	}

	/**
	 * Gets the data type required for the argument value
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument
	 */
	protected function getArgumentType(\TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument) {
		$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
		$controllerClassName = $command->getControllerClassName();
		$methodName = $command->getControllerCommandName() . 'Command';
		$tags = $this->reflectionService->getMethodTagsValues($controllerClassName, $methodName);
		foreach ($tags['param'] as $tag) {
			list ($argumentType, $argumentVariableName) = explode(' ', $tag);
			if (substr($argumentVariableName, 1) == $argument->getName()) {
				return $argumentType;
			}
		}
		return NULL;
	}

	/**
	 * Get a human-readable label for a command argument
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument
	 * @return string
	 */
	protected function getArgumentLabel(\TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument) {
		$argumentName = $argument->getName();
		list ($extensionName, $commandControllerName, $commandName) = explode(':', $this->task->getCommandIdentifier());
		$path = array('command', $commandControllerName, $commandName, 'arguments', $argumentName);
		$labelNameIndex = implode('.', $path);
		$label = $this->getLanguageLabel($labelNameIndex);
		if (!$label) {
			$label = 'Argument: ' . $argumentName;
		}
		$descriptionIndex = $labelNameIndex . '.description';
		$description = $this->getLanguageLabel($descriptionIndex);
		if (!$description) {
			$description = $argument->getDescription();
		}
		if ($description) {
			$label .= '. <em>' . htmlspecialchars($description) . '</em>';
		}
		return $label;
	}

	/**
	 * Gets the default value of argument
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument
	 * @return mixed
	 */
	protected function getDefaultArgumentValue(\TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument) {
		$type = $this->getArgumentType($argument);
		$argumentName = $argument->getName();
		$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
		$argumentReflection = $this->reflectionService->getMethodParameters($command->getControllerClassName(), $command->getControllerCommandName() . 'Command');
		$defaultValue = $argumentReflection[$argumentName]['defaultValue'];
		if ($type === 'boolean') {
			$defaultValue = ((bool) $defaultValue) ? 1 : 0;
		}
		return $defaultValue;
	}

	/**
	 * Get a human-readable label for the action field
	 *
	 * @return string
	 */
	protected function getActionLabel() {
		$index = 'task.action';
		$label = $this->getLanguageLabel($index, 'cicbase');
		if (!$label) {
			$label = 'CommandController Command. <em>Save and reopen to define command arguments</em>';
		}
		return $label;
	}

	/**
	 * Render a select field with name $name and options $options
	 *
	 * @param string $name
	 * @param array $options
	 * @param string $selectedOptionValue
	 * @return string
	 */
	protected function renderSelectField($name, array $options, $selectedOptionValue) {
		$html = array(
			'<select name="tx_scheduler[task_cicbase][' . $name . ']">'
		);
		foreach ($options as $optionValue=>$optionLabel) {
			$selected = $optionValue == $selectedOptionValue ? ' selected="selected"' : '';
			array_push($html, '<option title="test" value="' . htmlspecialchars($optionValue) . '"' . $selected . '>' . htmlspecialchars($optionLabel) . '</option>');
		}
		array_push($html, '</select>');
		return implode(LF, $html);
	}

	/**
	 * Renders a field for defining an argument's value
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $currentValue
	 * @return string
	 */
	protected function renderField(\TYPO3\CMS\Extbase\Mvc\Cli\CommandArgumentDefinition $argument, $currentValue) {
		$type = $this->getArgumentType($argument);
		$name = $argument->getName();
		$fieldName = 'tx_scheduler[task_cicbase][arguments][' . htmlspecialchars($name) . ']';
		if ($type == 'boolean') {
				// checkbox field for boolean values.
			$html = '<input type="hidden" name="' . $fieldName . '" value="0" />';
			$html .= '<input type="checkbox" name="' . $fieldName . '" value="1" ' . ($currentValue == 1 ? ' checked="checked"' : '') . '/>';
		} else {
				// regular string, also the default field type
			$html = '<input type="text" name="' . $fieldName . '" value="' . htmlspecialchars($currentValue) . '" /> ';
		}
		return $html;
	}

}

?>