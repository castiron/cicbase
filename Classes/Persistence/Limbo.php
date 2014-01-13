<?php
namespace CIC\Cicbase\Persistence;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
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

class Limbo implements \CIC\Cicbase\Persistence\LimboInterface {


	/**
	 * @var TYPO3\CMS\Core\Cache\CacheManager
	 * @inject
	 */
	protected $cacheManager;

	/**
	 * @param string $key
	 * @return string
	 */
	protected function getCacheKey($key = '') {
		$id = '';
		if ($GLOBALS['TSFE']->fe_user) {
			$id = $GLOBALS['TSFE']->fe_user->id . '_';
		}
		if ($key) {
			$key = str_replace('.', '_', $key);
		}
		return 'heldFile_'.$id.$key;

	}


	/**
	 * Returns the cache object
	 * @return mixed
	 * @throws \Exception
	 */
	protected function getCache() {
		try {
			$cache = $this->cacheManager->getCache('cicbase_cache');
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			throw new \Exception ('Unable to load the cicbase cache.');
		}
		return $cache;
	}


	/**
	 * @param string $key
	 * @return mixed|void
	 */
	public function clearHeld($key = '') {
		$cache = $this->getCache();
		$cache->remove($this->getCacheKey($key));
	}


	/**
	 * Returns the held file
	 * @param string $key
	 * @return \CIC\Cicbase\Persistence\LimboObjectInterface|null
	 */
	public function getHeld($key = '') {
		$cache = $this->getCache();
		$serializedData = $cache->get($this->getCacheKey($key));
		if($serializedData) {
			$limboObject = unserialize($serializedData);
			return $limboObject;
		} else {
			return NULL;
		}
	}


	/**
	 * @param \CIC\Cicbase\Persistence\LimboObjectInterface $limboObject
	 * @param string $key
	 * @return mixed|void
	 */
	public function hold(LimboObjectInterface $limboObject, $key = '') {
		$cache = $this->getCache();
		$cacheKey = $this->getCacheKey($key);
		$cache->set($cacheKey, serialize($limboObject), array('heldFile'), 3600);
	}

}




?>