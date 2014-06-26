<?php
namespace CIC\Cicbase\ViewHelpers;

class ComplexIfViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @param boolean $condition1
	 * @param boolean $condition2
	 * @param string $junction
	 * @param boolean $condition3
	 * @return string
	 * @throws \Exception
	 */
	public function render($condition1, $condition2, $junction, $condition3 = null) {
		$junction = strtolower($junction);
		switch($junction) {
			case 'and':
			case '&&':
				if($condition3 === null) {
					$true = $condition1 && $condition2;
				} else {
					$true = $condition1 && $condition2 && $condition3;
				}
				break;
			case 'or':
			case '||':
				if($condition3 === null) {
					$true = $condition1 || $condition2;
				} else {
					$true = $condition1 || $condition2 || $condition3;
				}
				break;
			default:
				throw new \Exception("The junction '$junction' is not recognized.");
		}

		if ($true) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}
?>