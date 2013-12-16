<?php
namespace CIC\Cicbase\ViewHelpers\Form;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

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

abstract class AbstractFormFieldViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {


	/**
	 * You can change this in a subclass to add
	 * a class to the label tag.
	 *
	 * @var string
	 */
	protected $labelClass = '';


	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('label', 'string', 'Creates a label element for the input element.', FALSE, '');
		$this->registerArgument('labelClass', 'string', 'Adds a css class to the label element.', FALSE, '');
		$this->registerArgument('description', 'string', 'Creates a p tag after the created input element.', FALSE, '');
	}

	/**
	 * Call the render() method and handle errors.
	 *
	 * @return string the rendered ViewHelper
	 */
	protected function callRenderMethod() {
		$id = $this->deriveId();
		if($id) {
			$this->tag->addAttribute('id', $id);
		}
		$inputTag = parent::callRenderMethod();
		$labelTag = $this->renderLabelTag();
		$descriptionTag = $this->renderDescriptionTag();
		$errors = $this->renderErrors();

		return $labelTag.$inputTag.$descriptionTag.$errors;
	}


	/**
	 * @param $tagName
	 * @param array $attributes
	 * @param string $content
	 * @param bool $forceClosingTag
	 * @return string
	 */
	protected function createTag($tagName, array $attributes = NULL, $content = '', $forceClosingTag = FALSE) {
		$tag = new TagBuilder($tagName, $content);
		if ($attributes) $tag->addAttributes($attributes);
		$tag->forceClosingTag($forceClosingTag);
		return $tag->render();
	}

	/**
	 * Overriding this class to look for errors not just
	 * on property fields, but named fields as well. That is,
	 * there are some variables passed to an action that undergo
	 * validation that aren't properties of an object, like a
	 * password field or credit card number. And these fields
	 * also need to show an error class.
	 */
	protected function setErrorClassAttribute() {
		if ($this->hasArgument('class')) {
			$cssClass = $this->arguments['class'] . ' ';
		} else {
			$cssClass = '';
		}

		if ($this->hasArgument('errorClass')) {
			$cssClass .= $this->arguments['errorClass'];
		} else {
			$cssClass .= 'error';
		}

		if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {

			// Check if this is a property that has errors
			$mappingResultsForProperty = $this->getMappingResultsForProperty();
			if ($mappingResultsForProperty->hasErrors()) {
				$this->tag->addAttribute('class', $cssClass);

			// Check if this is a named field that has errors
			} else if(isset($this->arguments['name'])) {
				$originalRequestMappingResults = $this->controllerContext->getRequest()->getOriginalRequestMappingResults();
				$property = str_replace('[]', '', $this->arguments['name']);
				$mappingResultsForName = $originalRequestMappingResults->forProperty($property);
				if($mappingResultsForName->hasErrors()){
					$this->tag->addAttribute('class', $cssClass);
				}

			}
		} else {
			// @deprecated since Extbase 1.4.0, will be removed in Extbase 1.6.0.
			$errors = $this->getErrorsForProperty();
			if (count($errors) > 0) {
				$this->tag->addAttribute('class', $cssClass);
			}
		}
	}

	/**
	 * Adds error messages below each field that has errors.
	 *
	 * @return string
	 */
	protected function renderErrors() {
		$objectName = $this->getFormObjectName();
		if(isset($this->arguments['property'])) {
			$property = $objectName.'.'.$this->arguments['property'];
		} else {
			$name = $this->arguments['name'];
			$property = str_replace('[]', '', $name);
		}

		$controllerName = lcfirst($this->controllerContext->getRequest()->getControllerName());
		$extensionName = lcfirst($this->controllerContext->getRequest()->getControllerExtensionName());

		// This has been tested with new property mapper enabled and disabled, but it's been _more_ tested
		// with the property mapper enabled --ZD
		if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {
			$validationResults = $this->controllerContext->getRequest()->getOriginalRequestMappingResults();
			$errors = $validationResults->forProperty($property)->getErrors();
		} else {
			// @deprecated since Extbase 1.4.0, will be removed in Extbase 1.6.0.
			$validationResults = $this->controllerContext->getRequest()->getErrors();
			$allErrors = $validationResults[$objectName]->getErrors();
			if(array_key_exists($this->arguments['property'], $allErrors) && is_object($allErrors[$this->arguments['property']])) {
				$errors = $allErrors[$this->arguments['property']]->getErrors();
			} else {
				$errors = array();
			}
		}

		$content = '';
		foreach($errors as $error) {
			$code = $error->getCode();
			$path = "form-$controllerName"."Controller-$property-$code";
			$key = 'LLL:EXT:'.$extensionName.'/Resources/Private/Language/locallang.xml:'.$path;
			$message = LocalizationUtility::translate($key, $extensionName);
			if(!$message) {
				$message = $error->getMessage();
			}
			$content .= $this->createTag('div', NULL, $message);
		}
		if($content){
			$innerWrap = $this->createTag('div', array('class' => 'error message'), $content);
			$errorWrap = $this->createTag('div', array('class' => 'errorWrap'), $innerWrap);
			return $errorWrap;
		}
		return '';
	}

	/**
	 * @return string
	 */
	protected function renderLabelTag() {
		if(!isset($this->arguments['label']) || $this->arguments['label'] == '') {
			return '';
		}
		$labelString = $this->arguments['label'];
		$id = $this->deriveId();
		$attributes = array();
		if($id) {
			$attributes['for'] = $id;
		}

		if(isset($this->arguments['labelClass']) && $this->arguments['labelClass']) {
			$attributes['class'] = ' '.$this->arguments['labelClass'];
		}

		return $this->createTag('label', $attributes, $labelString);

	}

	/**
	 * @return string
	 */
	protected function deriveId() {
		if(isset($this->arguments['id']) && $this->arguments['id']) {
			return $this->arguments['id'];
		}
		return '';
	}

	/**
	 * @return string
	 */
	protected function renderDescriptionTag() {
		if(!isset($this->arguments['description']) || $this->arguments['description'] == '') {
			return '';
		}
		return $this->createTag('p', array('class' => 'message'), $this->arguments['description']);
	}

	/**
	 * Convenience method for getting the name of the object that this form is about.
	 * @return object
	 */
	protected function getFormObjectName() {
		try {
			return $this->viewHelperVariableContainer->get('TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
		} catch(\Exception $e) {
			return '';
		}
	}
}
?>