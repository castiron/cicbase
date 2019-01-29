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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
 *     {yield->f:format.raw()}
 *   </div>
 * </div>
 *
 *
 */
class RenderViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper {
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
		$arguments['arguments']['yield'] = $renderChildrenClosure();
		return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
	}
}
