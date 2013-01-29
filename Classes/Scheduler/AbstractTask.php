<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Zach Davis <zach@castironcoding.com>, Cast Iron Coding
 *  Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding
 *  Gabe Blair <gabe@castironcoding.com>, Cast Iron Coding
 *  Peter Soots <peter@castironcoding.com>, Cast Iron Coding
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

abstract class Tx_Cicbase_Scheduler_AbstractTask extends tx_scheduler_Task {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Service_TypoScriptService
	 */
	protected $typoscriptService;


	/**
	 * inject the persistenceManager
	 *
	 * @param Tx_Extbase_Persistence_Manager persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * inject the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManager configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
	}


	/**
	 * inject the typoscriptService
	 *
	 * @param Tx_Extbase_Service_TypoScriptService typoscriptService
	 * @return void
	 */
	public function injectTyposcriptService(Tx_Extbase_Service_TypoScriptService $typoscriptService) {
		$this->typoscriptService = $typoscriptService;
	}


	/**
	 * A function for injecting dependencies. Should be called first
	 * thing within the overridden 'execute' method.
	 *
	 * @param $extensionName
	 * @param $pluginName
	 */
	protected function initialize($extensionName, $pluginName) {
		// Get ObjectManager
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->injectConfigurationManager($this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManager'));

		// Configure the object manager
		$typoScriptSetup = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (is_array($typoScriptSetup['config.']['tx_extbase.']['objects.'])) {
			$objectContainer = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
			foreach ($typoScriptSetup['config.']['tx_extbase.']['objects.'] as $classNameWithDot => $classConfiguration) {
				if (isset($classConfiguration['className'])) {
					$originalClassName = rtrim($classNameWithDot, '.');
					$objectContainer->registerImplementation($originalClassName, $classConfiguration['className']);
				}
			}
		}

		// Inject Depencencies
		$class = new ReflectionClass($this);
		$methods = $class->getMethods();
		foreach ($methods as $method) {
			if (substr_compare($method->name, 'inject', 0, 6) == 0) {
				$comment = $method->getDocComment();
				preg_match('#@param ([^\s]+)#', $comment, $matches);
				$type = $matches[1];
				$dependency = $this->objectManager->get($type);
				$method->invokeArgs($this, array($dependency));
			}
		}


		// Grab the settings array
		$this->configurationManager->setConfiguration(array('extensionName' => $extensionName, 'pluginName' => $pluginName));
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		if(!$this->settings) {
			$configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$settings = $configuration['plugin.']['tx_'.strtolower($extensionName).'.']['settings.'];
			$this->settings = $this->typoscriptService->convertTypoScriptArrayToPlainArray($settings);
		}

	}
}

?>