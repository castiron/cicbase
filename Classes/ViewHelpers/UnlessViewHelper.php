<?php

class Tx_Cicbase_ViewHelpers_UnlessViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if $condition is FALSE, otherwise renders <f:else> child.
	 *
	 * @param boolean $condition View helper condition
	 * @return string the rendered string
	 * @api
	 */
	public function render($condition) {
		if ($condition) {
			return $this->renderElseChild();
		} else {
			return $this->renderThenChild();
		}
	}
}
?>
