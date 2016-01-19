<?php

namespace CIC\Cicbase\View;

/**
 * Overriding the fluid template parser to:
 *
 * 1. Include custom namespaces by default, without needing to declare them in each template/partial
 *
 * Class TemplateParser
 * @package CIC\Cicbase\View
 */
class TemplateParser extends \TYPO3\CMS\Fluid\Core\Parser\TemplateParser {

	/** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface */
	protected $configurationManager;

	/** @var array */
	protected $extensionSettings = array();

	/**
	 * Namespace identifiers and their component name prefix (Associative array).
	 * @var array
	 * @override
	 */
	protected $namespaces = array();

	/**
	 * Resets the parser to its default values.
	 *
	 * @return void
	 * @override
	 */
	protected function reset() {
		if (!$this->hasCustomNamespaces()) {
			return parent::reset();
		}
		$this->namespaces = $this->getCustomNamespaces();
	}

	/**
	 * Extracts namespace definitions out of the given template string and sets
	 * $this->namespaces.
	 *
	 * @param string $templateString Template string to extract the namespaces from
	 * @return string The updated template string without namespace declarations inside
	 * @throws \TYPO3\CMS\Fluid\Core\Parser\Exception if a namespace can't be resolved or has been declared already
	 * @override
	 */
	protected function extractNamespaceDefinitions($templateString) {

		// ADDED STUFF HERE
		if (!$this->hasCustomNamespaces()) {
			return parent::extractNamespaceDefinitions($templateString);
		}

		$matches = array();
		preg_match_all(self::$SCAN_PATTERN_XMLNSDECLARATION, $templateString, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// skip reserved "f" namespace identifier
			if ($match['identifier'] === 'f') {
				continue;
			}
			if (array_key_exists($match['identifier'], $this->namespaces)) {
				throw new \TYPO3\CMS\Fluid\Core\Parser\Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']), 1331135889);
			}
			if (isset($this->settings['namespaces'][$match['xmlNamespace']])) {
				$phpNamespace = $this->settings['namespaces'][$match['xmlNamespace']];
			} else {
				$matchedPhpNamespace = array();
				if (preg_match(self::$SCAN_PATTERN_DEFAULT_XML_NAMESPACE, $match['xmlNamespace'], $matchedPhpNamespace) === 0) {
					continue;
				}
				$phpNamespace = str_replace('/', '\\', $matchedPhpNamespace['PhpNamespace']);
			}
			$this->namespaces[$match['identifier']] = $phpNamespace;
		}
		$matches = array();
		preg_match_all(self::$SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			if (array_key_exists($match['identifier'], $this->namespaces)) {

				// ADDED STUFF HERE -- You can now specify a different value for a namespace
				$existingPhpNamespace = $this->namespaces[$match['identifier']];
				if ($existingPhpNamespace != $match['phpNamespace']) {
					throw new \TYPO3\CMS\Fluid\Core\Parser\Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']), 1224241246);
				}
			} else {
				$this->namespaces[$match['identifier']] = $match['phpNamespace'];
			}
		}
		if ($matches !== array()) {
			$templateString = preg_replace(self::$SCAN_PATTERN_NAMESPACEDECLARATION, '', $templateString);
		}

		return $templateString;
	}

	/**
	 * @return bool
	 */
	protected function hasCustomNamespaces() {
		return isset($this->extensionSettings['fluidNamespaces']) && count($this->extensionSettings['fluidNamespaces']);
	}

	/**
	 * @return array
	 */
	protected function getCustomNamespaces() {
		$default = array('f' => 'TYPO3\CMS\Fluid\ViewHelpers');
		if (!$this->hasCustomNamespaces()) return $default;
		return array_merge($this->extensionSettings['fluidNamespaces'], $default);
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->extensionSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}
}