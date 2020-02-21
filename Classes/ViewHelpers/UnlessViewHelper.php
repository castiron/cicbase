<?php namespace CIC\Cicbase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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

	/**
	 * @param array $arguments
	 * @param RenderingContextInterface $renderingContext
	 * @return bool
	 */
	public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
	{
		if($arguments['condition']) {
			return false;
		}
		return true;
	}

}
