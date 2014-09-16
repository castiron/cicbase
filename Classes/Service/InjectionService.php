<?php

class Tx_Cicbase_Service_InjectionService extends \Tx_Extbase_Object_Container_Container {

	/**
	 * @var \Tx_Extbase_Object_ObjectManager
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
	 * Overriding this to let the REAL objectManager do the heavy lifting
	 *
	 * @param string $className
	 * @param array $givenConstructorArguments
	 * @return mixed|object
	 */
	protected function getInstanceInternal($className, $givenConstructorArguments = array()) {
		if (!$this->objectManager) {
			$this->objectManager = \t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		}
		array_unshift($givenConstructorArguments, $className);
		return call_user_func_array(array($this->objectManager, 'get'), $givenConstructorArguments);
	}


	/**
	 * This method WAS private in parent class...
	 *
	 * @param string $className
	 * @return \Tx_Extbase_Object_Container_ClassInfo
	 */
	protected function getClassInfo($className) {
		$classNameHash = md5($className);
		$classInfo = $this->getClassInfoCache()->get($classNameHash);
		if (!$classInfo instanceof \Tx_Extbase_Object_Container_ClassInfo) {
			$classInfo = $this->getClassInfoFactory()->buildClassInfoFromClassName($className);
			$this->getClassInfoCache()->set($classNameHash, $classInfo);
		}
		return $classInfo;
	}
}