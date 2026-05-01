<?php
namespace CIC\Cicbase\Service;

use CIC\Cicbase\Domain\Model\LatLng;
use CIC\Cicbase\Exception\GeolocationError;
use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Azure Maps backed geolocation service. Public API matches GeolocationService
 * (the Google-backed one) so callers can swap providers via config.
 */
class AzureGeolocationService {
    use ExtbaseInstantiable;

    protected $apiKey = false;
    protected $cache = false;
    protected $cacheLifetime = 3600;

    public function __construct($apiKey = false, $cacheLifetime = null) {
        $this->setApiKey($apiKey);
        $this->cache = $this->getCache();
        if ($cacheLifetime) {
            $this->cacheLifetime = $cacheLifetime;
        }
    }

    protected function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    protected function getApiKey() {
        return $this->apiKey;
    }

    /**
     * Returns Latitude and Longitude for an Address Object.
     *
     * @throws GeolocationError
     */
    public function getLatLng($address, $cacheFailures = false) {
        $addressString = $address->getFullAddressOneLine();
        $latLng = GeneralUtility::makeInstance(LatLng::class);

        $res = $this->geocode($addressString, $cacheFailures);

        $latLng->setLat($res->location->lat);
        $latLng->setLng($res->location->lng);

        return $latLng;
    }

    protected function getCache() {
        try {
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cicbase_cache');
        } catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
            // Unable to load
        }
        return $cache;
    }

    /**
     * Queries the Azure Maps geocoding API. Azure returns GeoJSON;
     * features[0].geometry.coordinates is [lon, lat]. We normalise to the same
     * ->location->lat / ->location->lng shape that the Google service exposes.
     */
    protected function geocode($address, $cacheFailures = false) {
        $key = $this->getApiKey();
        if (!$key) {
            throw new GeolocationError('Azure Maps subscription key not configured.');
        }

        $requestUrl = 'https://atlas.microsoft.com/geocode?api-version=2025-01-01'
            . '&query=' . urlencode($address)
            . '&subscription-key=' . urlencode($key);

        $cacheKey = 'geolocation_azure_' . md5($requestUrl);
        if ($this->cache->has($cacheKey)) {
            return unserialize($this->cache->get($cacheKey));
        }

        $res = file_get_contents($requestUrl);
        $out = false;
        if ($res) {
            $resObj = json_decode($res);
            $feature = isset($resObj->features[0]) ? $resObj->features[0] : null;
            $coords = $feature && isset($feature->geometry->coordinates) ? $feature->geometry->coordinates : null;

            if (is_array($coords) && count($coords) >= 2) {
                $out = new \stdClass();
                $out->location = new \stdClass();
                $out->location->lat = (float)$coords[1];
                $out->location->lng = (float)$coords[0];
                $matchCodes = isset($feature->properties->matchCodes) ? $feature->properties->matchCodes : array();
                $out->partial_match = is_array($matchCodes) && in_array('Ambiguous', $matchCodes, true);
            } else {
                throw new GeolocationError("Could not geolocate address: $address. No features returned.");
            }
        }

        if ($cacheFailures || ($out && $out->location->lat && $out->location->lng)) {
            $this->cache->set($cacheKey, serialize($out), array(), $this->cacheLifetime);
        }
        return $out;
    }
}
