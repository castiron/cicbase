<?php
namespace CIC\Cicbase\ViewHelpers;

	/*                                                                        *
	 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
	 *                                                                        *
	 * It is free software; you can redistribute it and/or modify it under    *
	 * the terms of the GNU Lesser General Public License, either version 3   *
	 *  of the License, or (at your option) any later version.                *
	 *                                                                        *
	 * The TYPO3 project - inspiring people to share!                         *
	 *                                                                        */

/**
 *
 * Same as Fluid's render VH but renders the body content of the render tag
 * and includes it in the partial/section arguments as a yield option.
 *
 * EXAMPLE
 * ---------
 *
 *
 * A render tag with body:
 *
 * <f:form action="search" controller="Publication" method="post">
 *   <c:render partial="FieldWrapper">
 *     <f:form.textfield name="keywords" />
 *   </c:render>
 * </f:form>
 *
 *
 * Partial:
 *
 * <div class="form-input">
 *   <div class="input control-group">
 *     {yield}
 *   </div>
 * </div>
 *
 *
 * NOTE: You can also specify the name of the {yield} variable.
 */
class RenderViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper {

	/**
	 * Renders the content.
	 *
	 * @param string $section Name of section to render. If used in a layout, renders a section of the main content file. If used inside a standard template, renders a section of the same file.
	 * @param string $partial Reference to a partial.
	 * @param array $arguments Arguments to pass to the partial.
	 * @param boolean $optional Set to TRUE, to ignore unknown sections, so the definition of a section inside a template can be optional for a layout
	 * @param string $yield The name of the variable passed to the section or argument for accessing the render tag's body.
	 * @return string
	 * @api
	 */
	public function render($section = NULL, $partial = NULL, $arguments = array(), $optional = FALSE, $yield = 'yield') {
		$arguments = $this->loadSettingsIntoArguments($arguments);

		$body = $this->renderChildren();

		if(trim($body)) {
			$arguments[$yield] = new RenderViewHelperStringObject($body);
		}

		if ($partial !== NULL) {
			return $this->viewHelperVariableContainer->getView()->renderPartial($partial, $section, $arguments);
		} elseif ($section !== NULL) {
			return $this->viewHelperVariableContainer->getView()->renderSection($section, $arguments, $optional);
		}
		return '';
	}

}

/**
 * If we simply pass a string for the value of a view variable, then it would
 * try to escape that string. So passing an object with a __toString() implementation
 * will prevent the string from being escaped.
 *
 */
class RenderViewHelperStringObject {
	/**
	 * @var string
	 */
	protected $content = '';

	public function __construct($content = '') {
		$this->content = $content;
	}

	public function __toString() {
		return $this->content;
	}
}

?>