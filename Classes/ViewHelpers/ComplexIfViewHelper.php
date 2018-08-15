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
	 * @return string
	 * @throws \Exception
	 */
	public function render() {
		$junction = strtolower($this->arguments['junction']);
		switch($junction) {
			case 'and':
			case '&&':
				if($this->arguments['condition3'] === null) {
					$true = $this->arguments['condition1'] && $this->arguments['condition2'];
				} else {
					$true = $this->arguments['condition1'] && $this->arguments['condition2'] && $this->arguments['condition3'];
				}
				break;
			case 'or':
			case '||':
				if($this->arguments['condition3'] === null) {
					$true = $this->arguments['condition1'] || $this->arguments['condition2'];
				} else {
					$true = $this->arguments['condition1'] || $this->arguments['condition2'] || $this->arguments['condition3'];
				}
				break;
			default:
				throw new \Exception("The junction '${junction}' is not recognized.");
		}

		if ($true) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}
?>
