<?php

class Tx_Cicbase_ViewHelpers_ComplexIfViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @param boolean $condition1
	 * @param boolean $condition2
	 * @param string $junction A string representation of a junction (i.e. 'and', '&&', 'or', or '||')
	 * @param boolean $condition3
	 * @return string the rendered string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
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
				throw new Exception("The junction '$junction' is not recognized.");
		}

		if ($true) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}
?>