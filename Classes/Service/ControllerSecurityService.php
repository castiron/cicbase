<?php
namespace CIC\Cicbase\Service;

/***************************************************************
 *  Copyright notice
 *  (c) 2010 Zach Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
 *              Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding, Inc
 *  All rights reserved
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
 * Controller for the Project object
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class ControllerSecurityService {

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * inject the reflectionService
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	public function secureActionArguments($arguments, $request, $controllerClassName) {

		// look for @modificationAllowed and @creationAllowed annotations on action methods
		$actionMethodName = $request->getControllerActionName().'Action';
		$tags = $this->reflectionService->getMethodTagsValues($controllerClassName, $actionMethodName);

		if(array_key_exists('modificationAllowed',$tags)) {
			$modificationAllowed = $tags['modificationAllowed'];
		} else {
			$modificationAllowed = array();
		}

		if (array_key_exists('creationAllowed', $tags)) {
			$creationAllowed = $tags['creationAllowed'];
		} else {
			$creationAllowed = array();
		}

		// modification and creation are disabled by default on all arguments
		foreach ($arguments->getArgumentNames() as $argumentName) {
			$conf = $arguments[$argumentName]->getPropertyMappingConfiguration();
			$options = array();

			$key = \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED;
			if(!in_array($argumentName, $modificationAllowed)) {
				$options[$key] = false;
			} else {
				$options[$key] = true;
			}

			$key = \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED;
			if (!in_array($argumentName, $creationAllowed)) {
				$options[$key] = false;
			} else {
				$options[$key] = true;
			}

			// set sane defaults
			#$conf->setTypeConverterOptions('TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter', $options);
		}
	}
}

?>