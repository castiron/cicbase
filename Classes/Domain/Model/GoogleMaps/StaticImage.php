<?php namespace CIC\Cicbase\Domain\Model\GoogleMaps;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use CIC\Cicbase\Utility\Arr;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class StaticImage
 * @package CIC\Cicbase\Domain\Model\GoogleMaps
 */
class StaticImage {
    use ExtbaseInstantiable;

    /**
     * @var array The arguments to the google static image URL (excluding markers)
     */
    var $args = array();

    /**
     * @var array
     */
    var $markers = array();

    var $allowedArgs = array(
        'center',
        'zoom',
        'size',
        'scale',
        'format',
        'maptype',
        'language',
        'region',
        'path',
        'visible',
        'style',
        'key',
        'signature'
    );

    /**
     * The base URL to the Google maps service
     */
    const API_URL = 'https://maps.googleapis.com/maps/api/staticmap';

    /**
     * StaticImage constructor.
     * @param array $args
     * @throws Exception
     */
    public function __construct($args = array()) {
        /**
         * If there's no key we cannot do anything
         */
        if (!$args['key']) {
            throw new Exception('Please provide a Google Maps API key.');
        }

        /**
         * If there's no size, Google will complain and not provide an image
         */
        if (!$args['size'] || !preg_match('~^\d+x\d+$~', $args['size'])) {
            throw new Exception('You must provide a size parameter like "200x300"');
        }

        /**
         * Add the markers (which must be instances of StaticImageMarker)
         */
        if ($args['markers']) {
            $this->addMarkers($args['markers']);
            unset($args['markers']);
        }

        /**
         * Add everything else that's allowed
         */
        $this->args = $this->filterArgs($args);
    }

    /**
     * @param $args
     * @return array
     */
    protected function filterArgs($args) {
        $keys = array_filter(array_keys($args), function($name) {
            // NB: for some reason passing a $name of 0 into "in_array()" gives you true ...
            return !is_int($name) && in_array($name, $this->allowedArgs);
        });
        return array_intersect_key($args, array_flip($keys));
    }

    /**
     * @param StaticImageMarker[] $markers
     */
    public function addMarkers($markers) {
        foreach ($markers as $marker) {
            $this->addMarker($marker);
        }
    }

    /**
     * @param $marker
     * @throws Exception
     */
    public function addMarker($marker) {
        if (!$marker instanceof StaticImageMarker) {
            throw new Exception('$marker must be in instance of StaticImageMarker');
        }
        $this->markers[] = $marker;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return static::API_URL . '?' . $this->toUrlParams();
    }

    /**
     * @return string
     */
    protected function toUrlParams() {
        $markersParams = array();
        /** @var StaticImageMarker $marker */
        foreach ($this->markers as $marker) {
            $markersParams[] = 'markers=' . $marker->toStaticMapsUrlParam();
        }
        $out = array(
            static::associativeArrayToParams(static::urlEncodeArray($this->args)),
            implode('&', $markersParams),
        );
        return implode('&', array_filter($out));
    }

    /**
     * @param $arr
     * @return string
     */
    protected static function associativeArrayToParams($arr) {
        $items = array();
        foreach ($arr as $k => $value) {
            $items[] = "$k=$value";
        }
        return implode('&', $items);
    }

    /**
     * @param $arr
     * @return array
     */
    protected static function urlEncodeArray($arr) {
        return array_map(function ($item) {
            return urlencode($item);
        }, $arr);
    }

    /**
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected function getCache() {
        return $this->cacheManager->getCache('cicbase_cache');
    }
}
