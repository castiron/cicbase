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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

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
 */
class RenderViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper {
	/**
	 * Renders the content.
	 *
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
	{

		$section = $arguments['section'];
		$partial = $arguments['partial'];
		$optional = $arguments['optional'];
		$arguments = static::loadSettingsIntoArguments($arguments['arguments'], $renderingContext);

		$arguments['yield'] = new RenderViewHelperStringObject($renderChildrenClosure());

		$viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
		if ($partial !== null) {
			return $viewHelperVariableContainer->getView()->renderPartial($partial, $section, $arguments);
		} elseif ($section !== null) {
			return $viewHelperVariableContainer->getView()->renderSection($section, $arguments, $optional);
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
