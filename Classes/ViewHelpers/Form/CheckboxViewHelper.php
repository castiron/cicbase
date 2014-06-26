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
 * A replacement for Fluid's checkbox viewhelper.
 */
class CheckboxViewHelper extends AbstractFormFieldViewHelper {

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
		$this->overrideArgument('value', 'array', 'Value of the checked input tag.', FALSE);
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
		$options = $this->getOptions();
		$multiple = count($options) > 1;
		foreach($this->getOptions() as $value => $labelString) {
			$inputTag = $this->renderInputTag($value, $multiple);
			$labelAttributes['class'] = 'checkbox';
			if($this->arguments['inline']) {
				$labelAttributes['class'] .= ' inline';
			}
			$content .= $this->createTag('label', $labelAttributes, $inputTag.' '.$labelString);
		}
		$hiddenField = $this->renderHiddenFieldForEmptyValue();

		return $content.$hiddenField;
	}

	/**
	 * @param string $value
	 * @param boolean $multiple
	 * @return string
	 */
	protected function renderInputTag($value, $multiple) {
		$name = $this->getName();
		$checked = FALSE;

		if ($this->isObjectAccessorMode()) {
			$propertyValue = $this->getPropertyValue();
			if ($propertyValue instanceof \Traversable) {
				$propertyValue = iterator_to_array($propertyValue);
			}
			if (is_array($propertyValue)) {
				if ($checked === NULL) {
					$checked = in_array($value, $propertyValue);
				}
				$multiple = TRUE;
			}elseif ($propertyValue !== NULL) {
				$checked = (boolean)$propertyValue === (boolean) $value;
			}
		} else if(isset($this->arguments['value'])) {
			$checked = in_array($value, $this->arguments['value']);
		}

		if($multiple) {
			$name .= '[]';
		}

		$this->registerFieldNameForFormTokenGeneration($name);
		$tag = new \TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder('input');
		$tag->addAttributes(array(
			'name' => $name,
			'value' => $value,
			'type' => 'checkbox'
		));
		if ($checked) {
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