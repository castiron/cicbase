<?php namespace CIC\Cicbase\Traits;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CacheableMethods
 * @package CIC\Cicbase\Traits
 */
trait CacheableMethods {
    /**
     * @var array
     */
    var $_cachedMethodResults = [];

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function getCached($method, $params) {
        return $this->_cachedMethodResults[static::makeKey($method, $params)];
    }

    /**
     * @param $methodName
     * @param $params
     * @return string
     */
    protected static function makeKey($methodName, $params) {
        return $methodName . GeneralUtility::shortMD5(serialize($params));
    }

    /**
     * @param $method
     * @return bool
     */
    protected function methodIsCached($method, $params) {
        return array_key_exists(static::makeKey($method, $params), $this->_cachedMethodResults);
    }

    /**
     * @param $method
     * @param $result
     * @param mixed $params Any additional params passed to the method that should uniquify this cache entry
     */
    protected function setCached($method, $result, $params) {
        $this->_cachedMethodResults[static::makeKey($method, $params)] = $result;
    }

    /**
     * @param $method
     * @param $params
     */
    protected function purge($method, $params) {
        if ($method) {
            unset($this->_cachedMethodResults[static::makeKey($method, $params)]);
            return;
        }
        $this->_cachedMethodResults = [];
    }
}
