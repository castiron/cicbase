<?php
namespace CIC\Cicbase\Validation\Validator;


/**
 * Class FluidStringValidator
 * @package CIC\Cicbase\Validation\Validator
 */
class FluidStringValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * @var \TYPO3\CMS\Fluid\Core\Parser\TemplateParser
	 * @inject
	 */
	protected $parser;

	/**
	 * This validator always needs to be executed even if the given value is empty.
	 * See AbstractValidator::validate()
	 *
	 * @var boolean
	 */
	protected $acceptsEmptyValues = FALSE;

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param mixed $value
	 * @return void
	 */
	protected function isValid($value) {
		try {
			$this->parser->parse($value);
		} catch (\Exception $e) {
			$this->addError("Could not parse fluid template: ".$e->getMessage(), 1418149684);
		}
	}


}
