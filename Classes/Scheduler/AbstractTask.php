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
		// Get ObjectManager
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->injectConfigurationManager($this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface'));

		// Configure the object manager
		$typoScriptSetup = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (is_array($typoScriptSetup['config.']['tx_extbase.']['objects.'])) {
			$objectContainer = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\Container\Container');
			foreach ($typoScriptSetup['config.']['tx_extbase.']['objects.'] as $classNameWithDot => $classConfiguration) {
				if (isset($classConfiguration['className'])) {
					$originalClassName = rtrim($classNameWithDot, '.');
					$objectContainer->registerImplementation($originalClassName, $classConfiguration['className']);
				}
			}
		}

		// Inject Depencencies
		$class = new \ReflectionClass($this);
		$methods = $class->getMethods();
		foreach ($methods as $method) {
			if (substr_compare($method->name, 'inject', 0, 6) == 0) {
				$comment = $method->getDocComment();
				preg_match('#@param ([^\s]+)#', $comment, $matches);
				$type = $matches[1];
				if(substr($type, 0, 1) == '\\') {
					$type = substr($type, 1);
				}
				$dependency = $this->objectManager->get($type);
				$method->invokeArgs($this, array($dependency));
			}
		}


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

?>