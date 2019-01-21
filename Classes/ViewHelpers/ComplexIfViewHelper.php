<?php
namespace CIC\Cicbase\ViewHelpers;

class ComplexIfViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('condition1', 'boolean', 'condition1', false, null);
        $this->registerArgument('condition2', 'boolean', 'condition2', false, null);
        $this->registerArgument('junction', 'string', '"and" or "or"', false, null);
        $this->registerArgument('condition3', 'boolean', 'condition3', false, null);
    }

	/**
	 * @param null $arguments
	 * @return bool
	 * @throws \Exception
	 */
	protected static function evaluateCondition($arguments = null)
	{
		$junction = strtolower($arguments['junction']);
		switch($junction) {
			case 'and':
			case '&&':
				if($arguments['condition3'] === null) {
					$true = $arguments['condition1'] && $arguments['condition2'];
				} else {
					$true = $arguments['condition1'] && $arguments['condition2'] && $arguments['condition3'];
				}
				break;
			case 'or':
			case '||':
				if($arguments['condition3'] === null) {
					$true = $arguments['condition1'] || $arguments['condition2'];
				} else {
					$true = $arguments['condition1'] || $arguments['condition2'] || $arguments['condition3'];
				}
				break;
			default:
				throw new \Exception("The junction '${junction}' is not recognized.");
		}

		return $true;
	}
}

