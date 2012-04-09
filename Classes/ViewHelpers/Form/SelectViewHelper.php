<?php

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

class Tx_Cicbase_ViewHelpers_Form_SelectViewHelper extends Tx_Fluid_ViewHelpers_Form_SelectViewHelper {


	/**
	 * Initialize the arguments that we're adding to the base class arguments.
	 * @return string rendered tag
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('nullOption', 'string', 'If specified, an extra option is added on top of the others labeled with the given string and valued as null');
	}

	/**
	 * Render the options tag, but with modifications if our special arguments have been specified.
	 * @return array an associative array of options, key will be the value of the option tag
	 */
	protected function getOptions() {
		$options = parent::getOptions();
		if($this->arguments['nullOption']) {
			$label = $this->arguments['nullOption'];
			$options = array('0' => $label) + $options;
		}
		return $options;
	}
}
