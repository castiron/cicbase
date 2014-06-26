<?php
namespace CIC\Cicbase\ViewHelpers\Form;
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
class RadioViewHelper extends AbstractFormFieldViewHelper {

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
		$this->overrideArgument('value', 'string', 'Value of the checked input tag.', FALSE);
		$this->registerArgument('options', 'string', 'The options for the radio list.', TRUE);
		$this->registerArgument('inline', 'boolean', 'Adds a CSS class "inline" to the radio labels.', FALSE);
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the radio.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$content = '';
		foreach($this->getOptions() as $value => $labelString) {
			$inputTag = $this->renderInputTag($value);
			$labelAttributes['class'] = 'radio';
			if($this->arguments['inline']) {
				$labelAttributes['class'] .= ' inline';
			}
			$content .= $this->createTag('label', $labelAttributes, $inputTag.' '.$labelString);
		}
		return $content;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	protected function renderInputTag($value) {
		$checked = FALSE;
		$propertyValue = FALSE;
		if ($this->isObjectAccessorMode()) {
			try {
				$propertyValue = $this->getPropertyValue();
			} catch (\TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException $exception) {
				// https://review.typo3.org/#/c/4413/4/Classes/ViewHelpers/Form/CheckboxViewHelper.php
				// http://forge.typo3.org/issues/5636
			}
			$checked = $propertyValue == $value; // not a type-safe comparison by intention
		}
		if($propertyValue == FALSE && ((isset($this->arguments['value']) && $this->arguments['value'] == $value)|| $this->getValue() == $value) ) {
			$checked = TRUE;
		}

		$tag = new \TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder('input');
		$tag->addAttributes(array(
			'value' => $value,
			'type' => 'radio',
			'name' => $this->getName(),
		));
		if($checked) {
			$tag->addAttribute('checked', 'checked');
		}

		return $tag->render();
	}

	/**
	 * An easily overridable function for getting options.
	 *
	 * @return mixed
	 */
	protected function getOptions() {
		return $this->arguments['options'];
	}

}
?>