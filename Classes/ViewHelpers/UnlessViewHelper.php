<?php
namespace CIC\Cicbase\ViewHelpers;


class UnlessViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if $condition is true, otherwise renders <f:else> child.
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
