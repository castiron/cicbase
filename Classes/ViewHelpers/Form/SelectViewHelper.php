<?php

namespace CIC\Cicbase\ViewHelpers\Form;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
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

class SelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

	/**
 	 *
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('nullOption', 'string', 'If specified, an extra option is added on top of the others labeled with the given string and valued as null');
		$this->registerArgument('dataPlaceholder', 'string', 'If specified, value will populate the select tag\'s data-placeholder attribute');
	}

	/**
	 * Render the tag.
	 *
	 * @return string rendered tag.
	 * @api
	 */
	public function render() {
		$name = $this->getName();

		if ($this->hasArgument('dataPlaceholder')) {
			$this->tag->addAttribute('data-placeholder', $this->arguments['dataPlaceholder']);
		}

		if ($this->hasArgument('multiple')) {
			$name .= '[]';
		}

		$this->tag->addAttribute('name', $name);

		$options = $this->getOptions();
		if (empty($options)) {
			$options = array('' => '');
		}
		$this->tag->setContent($this->renderOptionTags($options));

		$this->setErrorClassAttribute();

		$content = '';

		// register field name for token generation.
		// in case it is a multi-select, we need to register the field name
		// as often as there are elements in the box
		if ($this->hasArgument('multiple') && $this->arguments['multiple'] !== '') {
			$content .= $this->renderHiddenFieldForEmptyValue();
			for ($i = 0; $i < count($options); $i++) {
				$this->registerFieldNameForFormTokenGeneration($name);
			}
		} else {
			$this->registerFieldNameForFormTokenGeneration($name);
		}

		$content .= $this->tag->render();
		return $content;
	}

	/**
	 * Render the options tag, but with modifications if our special arguments have been specified.
	 * @return array an associative array of options, key will be the value of the option tag
	 */
	protected function getOptions() {
		$options = parent::getOptions();

		if($this->hasArgument('nullOption')) {
			$label = $this->arguments['nullOption'];
			$options = array('' => $label) + $options;
		}
		return $options;
	}
}
