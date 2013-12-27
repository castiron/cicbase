<?php
namespace CIC\Cicbase\ViewHelpers;

/**
 * Renders content if possible. Useful when rendering sections or partials that may or may not exist.
 */
class IfCanRenderViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if possible, otherwise renders <f:else> child.
	 *
	 * @return string the rendered string
	 * @api
	 */
	public function render() {

		try {
			$result = $this->renderThenChild();
		} catch(\TYPO3\CMS\Fluid\View\Exception\InvalidSectionException $e) {
			$result = $this->renderElseChild();
		}

		return $result;
	}
}

?>