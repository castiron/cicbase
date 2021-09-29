<?php
namespace CIC\Cicbase\Scheduler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Scheduler task to execute CommandController commands
 *
 * @package cicbase
 * @subpackage Scheduler
 */
class AbstractTask extends \TYPO3\CMS\Extbase\Scheduler\Task {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 */
	protected $typoscriptService;


	/**
	 * inject the persistenceManager
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * inject the configurationManager
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}


	/**
	 * inject the typoscriptService
	 *
	 * @param \TYPO3\CMS\Extbase\Service\TypoScriptService typoscriptService
	 * @return void
	 */
	public function injectTyposcriptService(\TYPO3\CMS\Extbase\Service\TypoScriptService $typoscriptService) {
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
		$injectionService = GeneralUtility::makeInstance('CIC\Cicbase\Service\InjectionService');
		$injectionService->doInjection($this);

		// Grab the settings array
		$this->configurationManager->setConfiguration(array('extensionName' => $extensionName, 'pluginName' => $pluginName));
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		if(!$this->settings) {
			$configuration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$settings = $configuration['plugin.']['tx_'.strtolower($extensionName).'.']['settings.'];
			$this->settings = $this->typoscriptService->convertTypoScriptArrayToPlainArray($settings);
		}

	}
}