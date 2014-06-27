<?php

namespace CIC\Cicbase\Validation\Validator;

/***************************************************************
 *  Copyright notice
 *  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
 *  All rights reserved
 *  This class is a backport of the corresponding class of FLOW3.
 *  All credits go to the v5 team.
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Validator for email addresses
 *
 * @package CIC\Cicbase
 * @subpackage Validation\Validator
 * @version $Id$
 */
class EmailAddressAllowEmptyValidator extends \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator {

	/**
	 * Checks if the given value is a valid email address.
	 * If at least one error occurred, the result is FALSE.
	 * The regexp is a modified version of the last one shown on
	 * http://www.regular-expressions.info/email.html
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($value) {
		if(empty($value)) {
			return true;
		} else {
			return parent::isValid($value);
		}
	}
}

?>