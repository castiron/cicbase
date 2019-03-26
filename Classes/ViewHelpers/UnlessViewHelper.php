<?php namespace CIC\Cicbase\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

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

    /**
     * @param $condition
     * @return mixed|string
     */
    public function render()
    {
        if ($this->arguments['condition']) {
            return $this->renderElseChild();
        } else {
            return $this->renderThenChild();
        }
    }
}
