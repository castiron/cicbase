<?php

namespace CIC\Cicbase\ViewHelpers;
use CIC\Cicbase\Utility\Arr;

/**
 * Allows you to more easily, incorporate flexform values into your fluid templates:
 *
 * <c:flexText field="application.overview.description">Some default description here</c:flexText>
 *
 * As you can see, there is a default value within the tag. You can also get field values via a path.
 * If a value was provided in the flexform, that will be used instead.
 *
 *
 * IMPORTANT: by default, we assume the flexform values are prefixed with "pluginConfiguration". So
 * the full path of "application.overview.description" in the flexform would really be:
 *
 * <settings.pluginConfiguration.application.overview.description>
 *   ...
 * </settings.pluginConfiguration.application.overview.description>
 *
 * This is to avoid accidentally overwriting actual typoscript settings with our flexform values.
 *
 *
 * Class SettingsViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class FlexTextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController contains a backup of the current $GLOBALS['TSFE'] if used in BE mode
	 */
	protected $tsfeBackup;

	/**
	 * Don't escape the the output
	 *
	 * @var boolean
	 */
	protected $escapeOutput = false;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;


	/**
	 * @param string $field
	 * @param string $parseFuncTSPath
	 * @param bool $raw
	 * @return string
	 */
	public function render($field, $parseFuncTSPath = 'lib.parseFunc_RTE', $raw = FALSE) {
		if (!$this->templateVariableContainer->exists('settings')) {
			return $this->renderChildren();
		}
		$settings = $this->templateVariableContainer->get('settings');
		if (isset($settings['pluginConfiguration']) && is_array($settings['pluginConfiguration'])) {
			$val = Arr::safePath($settings['pluginConfiguration'], $field);
			if ($val && $val !== '') {
				if ($raw) return $val;

				$this->simulateFrontendEnvironment();
				$content = $this->contentObject->parseFunc($val, array(), '< ' . $parseFuncTSPath);
				$this->resetFrontendEnvironment();

				return $content;
			}
		}

		return $this->renderChildren();
	}


	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	/**
	 * Copies the specified parseFunc configuration to $GLOBALS['TSFE']->tmpl->setup in Backend mode
	 * This somewhat hacky work around is currently needed because the parseFunc() function of \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer relies on those variables to be set
	 *
	 * @return void
	 */
	protected function simulateFrontendEnvironment() {
		if (TYPO3_MODE === 'BE') {
			$this->tsfeBackup = isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
			$GLOBALS['TSFE'] = new \stdClass();
			$GLOBALS['TSFE']->tmpl = new \stdClass();
			$GLOBALS['TSFE']->tmpl->setup = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		}
	}

	/**
	 * Resets $GLOBALS['TSFE'] if it was previously changed by simulateFrontendEnvironment()
	 *
	 * @return void
	 * @see simulateFrontendEnvironment()
	 */
	protected function resetFrontendEnvironment() {
		if (TYPO3_MODE === 'BE') {
			$GLOBALS['TSFE'] = $this->tsfeBackup;
		}
	}
}