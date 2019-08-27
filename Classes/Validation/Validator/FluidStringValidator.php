<?php
namespace CIC\Cicbase\Validation\Validator;


use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\AbstractView;

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
		    $view = GeneralUtility::makeInstance(StandaloneView::class);
		    $renderingContext = GeneralUtility::makeInstance(RenderingContext::class, $view);
		    $renderingContext->setViewHelperResolver(GeneralUtility::makeInstance(ViewHelperResolver::class));
		    $this->parser->setRenderingContext($renderingContext);
			$this->parser->parse($value);
		} catch (\Exception $e) {
			$this->addError("Could not parse fluid template: ".$e->getMessage(), 1418149684);
		}
	}


}
