<?php namespace CIC\Cicbase\Scheduler;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Scheduler task to execute CommandController commands
 *
 * @package cicbase
 * @subpackage Scheduler
 */
class AbstractTask extends \TYPO3\CMS\Extbase\Scheduler\Task {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 * @inject
	 */
	protected $typoscriptService;

	/**
	 * AbstractTask constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->configurationManager = $this->objectManager->get(ConfigurationManager::class);
		$this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
		$this->typoscriptService = $this->objectManager->get(TypoScriptService::class);
	}

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
		$this->configurationManager = $this->objectManager->get(ConfigurationManager::class);
		$this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
		$this->typoscriptService = $this->objectManager->get(TypoScriptService::class);		$this->configurationManager->setConfiguration(array('extensionName' => $extensionName, 'pluginName' => $pluginName));
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		if(!$this->settings) {
			$configuration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$settings = $configuration['plugin.']['tx_'.strtolower($extensionName).'.']['settings.'];
			$this->settings = $this->typoscriptService->convertTypoScriptArrayToPlainArray($settings);
		}
	}
}

