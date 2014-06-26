<?php
namespace CIC\Cicbase\Service;

/***************************************************************
 *  Copyright notice
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


class JsonObjectService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypeHandlingService
	 */
	protected $typeHandlingService;

	/**
	 * inject the typeHandlingService
	 *
	 * @param \TYPO3\CMS\Extbase\Service\TypeHandlingService typeHandlingService
	 * @return void
	 */
	public function injectTypeHandlingService(\TYPO3\CMS\Extbase\Service\TypeHandlingService $typeHandlingService) {
		$this->typeHandlingService = $typeHandlingService;
	}

	/**
	 * inject the reflectionService
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}


	/**
	 * @param $model
	 * @return \stdClass
	 */
	public function transform($model) {
		$tag = microtime();
		$class = get_class($model);
		if($class == 'TYPO3\CMS\Extbase\Persistence\ObjectStorage' || $class == 'TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage') {
			$out = array();
			foreach($model as $subModel) {
				$out[] = $this->transform($subModel);
			}
			return $out;
		} elseif(strpos($class,'Domain_Model') !== FALSE) {
			$transformedObject = new \stdClass;
			$properties = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getGettablePropertyNames($model);
			foreach ($properties as $property) {
				$getMethodName = 'get' . ucfirst($property);
				$methodTags = $this->reflectionService->getMethodTagsValues($class, $getMethodName);
				// The Goal here is to be able to expose properties and methods with the JSONExpose annotation.
				if ($property == 'uid' || array_key_exists('JSONExpose', $methodTags) || $this->reflectionService->isPropertyTaggedWith($class, $property, 'JSONExpose')) {
					$value = $model->$getMethodName();

					// TODO, not sure about this check for lazy loading. Would be good to write a test for it.
					if ($value instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
						$transformedObject->$property = 'lazy';
					} elseif ($this->typeHandlingService->isSimpleType(gettype($value))) {
						$transformedObject->$property = $value;
					} elseif (is_object($value)) {
						if ($value instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
							$transformedObject->$property = $this->transform($value);
						} else {
							$transformedObject->$property = get_class($value);
						}
					}
				}
			}
			return $transformedObject;
		} else {
			return NULL;
		}
	}


}

?>