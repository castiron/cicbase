<?php

/***************************************************************
 *  Copyright notice
 *  (c) 2012 Zach Davis <zach
 * @castironcoding.com>, Cast Iron Coding
 *  Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding
 *  Gabe Blair <gabe@castironcoding.com>, Cast Iron Coding
 *  Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later

 */
class Tx_Cicbase_Domain_Repository_BaseRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var integer
	 */
	protected $internalPid;

	/**
	 * inject the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Adds the ability to set storagePids for any domain object using typoscript:
	 * config.extBase.persistence.classes.CLASSNAME.storagePid
	 *
	 * Not implemented here, but you should also set the newRecordStoragePid too:
	 * config.extBase.persistence.classes.CLASSNAME.newRecordStoragePid
	 */
	public function initializeObject() {
		$frameworkConfig = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$ext = $frameworkConfig['extensionName'];
		$plugin = $frameworkConfig['pluginName'];
		$className = $this->objectType;

		if (!$ext || !$plugin) return;

		if (isset($frameworkConfig['persistence']['classes'][$className]) && !empty($frameworkConfig['persistence']['classes'][$className]['storagePid'])) {
			$this->defaultQuerySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
			$this->defaultQuerySettings->setStoragePageIds(t3lib_div::intExplode(',', $frameworkConfig['persistence']['classes'][$className]['storagePid']));
		}
	}
}

?>