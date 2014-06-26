<?php
namespace CIC\Cicbase\Service;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Zach Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
*  			Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding, Inc
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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

/**
 * Controller for the Project object
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class GeolocationService {

	protected $apiKey = false;
	protected $cache = false;


	public function __construct($apiKey = false) {
		$this->setApiKey($apiKey);
		$this->cache = $this->getCache();
	}

	protected function setApiKey($apiKey) {
		$this->apiKey = $apiKey;
	}

	protected function getApiKey() {
		return $this->apiKey;
	}

	/**
	 * Returns Latitude and Longitude for an Address Object
	 *
	 * @param \CIC\Cicbase\Domain\Model\Address $address The address to geocode
	 * @return \CIC\Cicbase\Domain\Model\LatLng
	 */
	public function getLatLng($address) {

		// get the address string
		$addressString = $address->getFullAddressOneLine();

		// construct the latLng object to return
		$latLng = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('CIC\Cicbase\Domain\Model\LatLng');

		// do the query
		$res = $this->geocode($addressString);

		$latLng->setLat($res->location->lat);
		$latLng->setLng($res->location->lng);

		return $latLng;
	}

	protected function getCache() {
		try {
			$cache = $GLOBALS['typo3CacheManager']->getCache('cicbase_cache');
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			// Unable to load
		}
		return $cache;
	}

	/**
	 * Queries Google geocoding API for lat and lng
	 *
	 * @param string The address to geocode in string format
	 * @return array The results of the Google geocoding API query
	 */
	protected function geocode($address) {
		$urlBase = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=';
		$addressParamValue = urlencode($address);
		$requestUrl = $urlBase.$addressParamValue;

		$cacheKey = 'geolocation_'.md5($requestUrl);
		if($this->cache->has($cacheKey)) {
			$out = unserialize($this->cache->get($cacheKey));
		} else {
			$res = file_get_contents($requestUrl);
			$out = FALSE;
			if($res) {
				$resObj = json_decode($res);
				if($resObj->status == 'OK') {
					$out = $resObj->results[0]->geometry;
					$out->partial_match = $resObj->results[0]->partial_match;
				}
			}

			if($out->location->lat && $out->location->lng) $this->cache->set($cacheKey,serialize($out),array(),3600);
		}
		return $out;
	}

}

?>