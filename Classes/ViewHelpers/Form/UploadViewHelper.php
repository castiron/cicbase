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
 * A replacement for Fluid's textfield viewhelper.
 */
class UploadViewHelper extends AbstractFormFieldViewHelper {
	/**
	 * @var string
	 */
	protected $tagName = 'input';

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
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the textfield.
	 *
	 * @param boolean $multiple
	 * @return string
	 * @api
	 */
	public function render($multiple = FALSE) {
		$name = $this->getName();
		$multipleString = $multiple ? '[]' : '';
		$allowedFields = array('name', 'type', 'tmp_name', 'error', 'size');
		foreach ($allowedFields as $fieldName) {
			$this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']');
		}

		$this->tag->addAttribute('type', 'file');
		$this->tag->addAttribute('name', $name . $multipleString);

		$this->setErrorClassAttribute();
		$uploadField = $this->tag->render();
		$valueFields = '';

		$value = $this->getValueAttribute();

		// If we have a value, render hidden input elements to
		// preserve the value in case nothing is uploaded.
		// We'll get actual objects on first form load,
		// but if there are validation errors, we'll only get
		// form data which we must sort through.
		if ($value) {

			$values = array();

			if ($multiple) {
				$i = 1;
				foreach ($value as $v) {
					if ($v instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
						$values[$v->getUid()] = $name . '[' . $i . ']';
					} elseif (is_numeric($v)) {
						$values[$v] = $name . '[' . $i . ']';
					}
					++$i;
				}
			} elseif ($value instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
				$values[$value->getUid()] = $name . '[valueIfEmpty]';
			} elseif (is_array($value) && isset ($value['valueIfEmpty'])) {
				$values[$value['valueIfEmpty']] = $name . '[valueIfEmpty]';
			}

			foreach ($values as $uid => $name) {
				$attributes = array(
					'type' => 'hidden',
					'name' => $name,
					'value' => $uid,
				);
				$valueFields .= $this->createTag('input', $attributes);
			}

		}


		return $valueFields . $uploadField;
	}
}
?>
