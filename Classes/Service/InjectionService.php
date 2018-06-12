<?php

namespace CIC\Cicbase\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InjectionService
 * @package CIC\Cicbase\Service
 */
class InjectionService extends \TYPO3\CMS\Extbase\Object\Container\Container {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * A public wrapper for the protected injectDependencies function
	 *
	 * @param mixed $instance
	 */
	public function doInjection($instance) {
		$className = get_class($instance);
		$classInfo = $this->getClassInfo($className);
		$this->injectDependencies($instance, $classInfo);
	}

    /**
     * @param object $instance
     * @param \TYPO3\CMS\Extbase\Object\Container\ClassInfo $classInfo
     */
    protected function initializeObject($instance, \TYPO3\CMS\Extbase\Object\Container\ClassInfo $classInfo)
    {
        /**
         * Noop
         */
    }


    /**
	 * Overriding this to let the REAL objectManager do the heavy lifting
	 *
	 * @param string $className
	 * @param array $givenConstructorArguments
	 * @return mixed|object
	 */
	protected function getInstanceInternal($className, $givenConstructorArguments = array()) {
		if (!$this->objectManager) {
			$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		}
		array_unshift($givenConstructorArguments, $className);
		return call_user_func_array(array($this->objectManager, 'get'), $givenConstructorArguments);
	}


	/**
	 * This method WAS private in parent class...
	 *
	 * @param string $className
	 * @return \TYPO3\CMS\Extbase\Object\Container\ClassInfo
	 */
	protected function getClassInfo($className) {
		$classNameHash = md5($className);
		$classInfo = $this->getClassInfoCache()->get($classNameHash);
		if (!$classInfo instanceof \TYPO3\CMS\Extbase\Object\Container\ClassInfo) {
			$classInfo = $this->getClassInfoFactory()->buildClassInfoFromClassName($className);
			$this->getClassInfoCache()->set($classNameHash, $classInfo);
		}
		return $classInfo;
	}
}
