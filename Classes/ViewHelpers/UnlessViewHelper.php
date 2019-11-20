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
        $this->registerArgument('condition', 'boolean', 'Condition expression conforming to Fluid boolean rules', false, false);
    }

    /**
     *
     * @param array $arguments
     * @return mixed
     */
    protected static function evaluateCondition($arguments = null) {
        return !(boolean) $arguments['condition'];
    }
}
