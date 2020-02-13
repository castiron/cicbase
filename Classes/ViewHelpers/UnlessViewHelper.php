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

    /**
     *
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        // Check is needed due to different behavior between local and staging/prod environments.
        if(!array_key_exists('condition', $this->argumentDefinitions)) {
            $this->registerArgument('condition', 'boolean', 'Condition expression conforming to Fluid boolean rules', false, false);
        }
    }

	public function render()
	{
		if ($this->arguments['condition']) {
			return $this->renderElseChild();
		} else {
			return $this->renderThenChild();
		}
	}
}
