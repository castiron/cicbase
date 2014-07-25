<?php
namespace CIC\Cicbase\Validation\Validator;

/**
 * Overriding the default email address validator to get some trimming happening
 *
 * Class EmailAddressValidator
 * @package CIC\Cicbase\Validation\Validator
 */
class EmailAddressValidator extends \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator {

	/**
	 * @param string $emailAddress
	 * @return bool
	 */
	protected function validEmail($emailAddress) {
		$res = parent::validEmail(trim($emailAddress));
		if (!$res) {
			$a = 1;
		}
		return $res;
	}
}
