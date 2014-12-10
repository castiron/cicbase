<?php
namespace CIC\Cicbase\ViewHelpers\Form;


class SelectWithBodyViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'select';


	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('multiple', 'string', 'if set, multiple select field');
		$this->registerTagAttribute('optionCount', 'int', 'Required for multiple select');
		$this->registerTagAttribute('size', 'string', 'Size of input field');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
	}

	/**
	 * Render the tag.
	 *
	 * @return string rendered tag.
	 * @throws
	 */
	public function render() {
		$name = $this->getName();
		if ($this->hasArgument('multiple')) {
			if (!$this->hasArgument('optionCount')) {
				throw new \Exception("You must specify an optionCount when using the 'multiple' argument for cic:selectWithBody");
			}
			$name .= '[]';
		}
		$this->tag->addAttribute('name', $name);
		$options = $this->renderChildren();
		$value = $this->getValue();
		if ($value) {
			if (is_array($value)) {
				foreach ($value as $val) {
					$options = preg_replace("/(value=\"$val\")/", "$1 selected=\"selected\"", $options);
				}
			} else {
				$options = preg_replace("/(value=\"$value\")/", "$1 selected=\"selected\"", $options);
			}
		}
		$this->tag->setContent($options);
		$this->setErrorClassAttribute();
		$content = '';
		// register field name for token generation.
		// in case it is a multi-select, we need to register the field name
		// as often as there are elements in the box
		if ($this->hasArgument('multiple') && $this->arguments['multiple'] !== '') {
			$content .= $this->renderHiddenFieldForEmptyValue();
			$optionCount = $this->arguments['optionCount'];
			for ($i = 0; $i < $optionCount; $i++) {
				$this->registerFieldNameForFormTokenGeneration($name);
			}
		} else {
			$this->registerFieldNameForFormTokenGeneration($name);
		}
		$content .= $this->tag->render();
		return $content;
	}

}


