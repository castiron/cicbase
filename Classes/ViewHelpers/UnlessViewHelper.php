<?php namespace CIC\Cicbase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class UnlessViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class UnlessViewHelper extends AbstractConditionViewHelper
{
	/**
	 * @var bool
	 */
	protected $escapeOutput = false;

	public function render()
	{
		if ($this->arguments['condition']) {
			return $this->renderElseChild();
		} else {
			return $this->renderThenChild();
		}
	}
}
