<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Zach Davis <zach@castironcoding.com>, Cast Iron Coding
 *  Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding
 *  Gabe Blair <gabe@castironcoding.com>, Cast Iron Coding
 *  Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A replacement for Fluid's radio viewhelper.
 */
class Tx_Cicbase_ViewHelpers_Form_RadioViewHelper extends Tx_Cicbase_ViewHelpers_Form_AbstractFormFieldViewHelper {
	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Wraps the input inside of the label element.
	 * @var bool
	 */
	protected $wrapInputWithLabel = TRUE;

	/**
	 * @var string
	 */
	protected $labelClass = 'radio';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->overrideArgument('value', 'string', 'Value of input tag. Required for radio buttons', TRUE);
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the radio.
	 *
	 * @param boolean $checked Specifies that the input element should be preselected
	 *
	 * @return string
	 * @api
	 */
	public function render($checked = NULL) {
		$this->tag->addAttribute('type', 'radio');

		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($checked === NULL && $this->isObjectAccessorMode()) {
			try {
				$propertyValue = $this->getPropertyValue();
			} catch (Tx_Fluid_Core_ViewHelper_Exception_InvalidVariableException $exception) {
				// https://review.typo3.org/#/c/4413/4/Classes/ViewHelpers/Form/CheckboxViewHelper.php
				// http://forge.typo3.org/issues/5636
				$propertyValue = FALSE;
			}
			// no type-safe comparisation by intention
			$checked = $propertyValue == $valueAttribute;
		}

		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}

		$this->setErrorClassAttribute();

		return $this->tag->render();
	}

}
?>