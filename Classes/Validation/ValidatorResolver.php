<?php

namespace CIC\Cicbase\Validation;

class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver {



	protected function buildBaseValidatorConjunction($indexKey, $targetClassName, array $validationGroups = array()) {
		$conjunctionValidator = new \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator();
		$this->baseValidatorConjunctions[$indexKey] = $conjunctionValidator;
		if (class_exists($targetClassName)) {
			// Model based validator
			/** @var \TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator $objectValidator */
			$objectValidator = $this->objectManager->get('TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator', array());
			foreach ($this->reflectionService->getClassPropertyNames($targetClassName) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflectionService->getPropertyTagsValues($targetClassName, $classPropertyName);

				if (!isset($classPropertyTagsValues['var'])) {
					throw new \InvalidArgumentException(sprintf('There is no @var annotation for property "%s" in class "%s".', $classPropertyName, $targetClassName), 1363778104);
				}





				// ONLY THING DIFFERENT THAN PARENT IMPLEMENTATION
				if (isset($classPropertyTagsValues['ignorevalidation'])) {
					continue;
				}








				try {
					$parsedType = \TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::parseType(trim(implode('', $classPropertyTagsValues['var']), ' \\'));
				} catch (\TYPO3\CMS\Extbase\Utility\Exception\InvalidTypeException $exception) {
					throw new \InvalidArgumentException(sprintf(' @var annotation of ' . $exception->getMessage(), 'class "' . $targetClassName . '", property "' . $classPropertyName . '"'), 1315564744, $exception);
				}
				$propertyTargetClassName = $parsedType['type'];
				if (\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::isCollectionType($propertyTargetClassName) === TRUE) {
					$collectionValidator = $this->createValidator('TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator', array('elementType' => $parsedType['elementType'], 'validationGroups' => $validationGroups));
					$objectValidator->addPropertyValidator($classPropertyName, $collectionValidator);
				} elseif (class_exists($propertyTargetClassName) && !\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::isCoreType($propertyTargetClassName) && $this->objectManager->isRegistered($propertyTargetClassName) && $this->objectManager->getScope($propertyTargetClassName) === \TYPO3\CMS\Extbase\Object\Container\Container::SCOPE_PROTOTYPE) {
					$validatorForProperty = $this->getBaseValidatorConjunction($propertyTargetClassName, $validationGroups);
					if (count($validatorForProperty) > 0) {
						$objectValidator->addPropertyValidator($classPropertyName, $validatorForProperty);
					}
				}

				$validateAnnotations = array();
				// @todo: Resolve annotations via reflectionService once its available
				if (isset($classPropertyTagsValues['validate']) && is_array($classPropertyTagsValues['validate'])) {
					foreach ($classPropertyTagsValues['validate'] as $validateValue) {
						$parsedAnnotations = $this->parseValidatorAnnotation($validateValue);

						foreach ($parsedAnnotations['validators'] as $validator) {
							array_push($validateAnnotations, array(
								'argumentName' => $parsedAnnotations['argumentName'],
								'validatorName' => $validator['validatorName'],
								'validatorOptions' => $validator['validatorOptions']
							));
						}
					}
				}

				foreach ($validateAnnotations as $validateAnnotation) {
					// @todo: Respect validationGroups
					$newValidator = $this->createValidator($validateAnnotation['validatorName'], $validateAnnotation['validatorOptions']);
					if ($newValidator === NULL) {
						throw new Exception\NoSuchValidatorException('Invalid validate annotation in ' . $targetClassName . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validateAnnotation->type . '".', 1241098027);
					}
					$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
				}
			}

			if (count($objectValidator->getPropertyValidators()) > 0) {
				$conjunctionValidator->addValidator($objectValidator);
			}
		}

		$this->addCustomValidators($targetClassName, $conjunctionValidator);
	}

}