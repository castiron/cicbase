<?php
namespace CIC\Cicbase\ViewHelpers;

/**
 * Renders stuff from the current tt_content record
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class TtViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;


	/**
	 * @param string $col
	 * @return integer
	 */
	public function render($col) {
		$cObj = $this->configurationManager->getContentObject();
		return $cObj->getData("field : $col");
	}
}

?>