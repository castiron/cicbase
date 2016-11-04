<?php namespace CIC\Cicbase\Domain\Model\GoogleMaps;

use CIC\Cicbase\Domain\Model\LatLng;
use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class StaticImageMarker
 * @package CIC\Cicbase\Domain\Model\GoogleMaps
 */
class StaticImageMarker {
    use ExtbaseInstantiable;

    /**
     * @var string
     */
    var $size = '';

    /**
     * @var
     */
    var $color = '';

    /**
     * @var string
     */
    var $label = '';

    /**
     * @var LatLng[]
     */
    var $locations = array();

    /**
     * StaticImageMarker constructor.
     * @param array $args
     * @throws Exception
     */
    public function __construct($args = array()) {
        $this->size = $args['size'] ?: $this->size;
        $this->color = $args['color'] ?: $this->color;
        $this->label = $args['label'] ?: $this->label;
        if ($locations = $args['locations']) {
            if (!is_array($locations)) {
                throw new Exception('"locations" parameter must be an array');
            }
            if (static::validateLocations($args['locations'])) {
                $this->locations = $locations;
            } else {
                throw new Exception('"locations" parameter must be an array of LatLang objects');
            }
        }
    }

    /**
     * @param $locations
     * @return bool
     */
    protected static function validateLocations($locations) {
        foreach ($locations as $location) {
            if (!$location instanceof LatLng) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function toParamsArray() {
        return array_filter(array(
            'color' => $this->color,
            'size' => $this->size,
            'label' => $this->label,
            'locations' => array_map(function($latLng) {
                return $latLng->getStringTuple();
            }, $this->locations),
        ));
    }

    /**
     * Get the params ready for a URL (already urlencoded)
     * @return string
     */
    public function toStaticMapsUrlParam() {
        $out = array();
        $params = $this->toParamsArray();
        foreach ($params as $paramName => $value) {
            /**
             * We'll deal with you later
             */
            if ($paramName === 'locations') {
                continue;
            }
            $out[] = $paramName . ':' . urlencode($value);
        }
        $separator = urlencode('|');
        if ($params['locations']) {
            $out[] = implode($separator, $params['locations']);
        }

        return implode($separator, $out);
    }
}
