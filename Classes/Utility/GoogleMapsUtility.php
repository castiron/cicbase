<?php namespace CIC\Cicbase\Utility;

/**
 * Class GoogleMapsUtility
 * @package CIC\Cicbase\Utility
 */
class GoogleMapsUtility {
    const GOOGLE_ONE_MILE_ZOOM_LEVEL = 14;

    /**
     * Using zoom = 14 - ln(radius)/ln(2)
     * Credit: http://jeffjason.com/2011/12/google-maps-radius-to-zoom/
     *
     * @param int|float $radius in miles
     * @return string
     */
    public static function radiusToZoom($radius) {
        return ceil(static::GOOGLE_ONE_MILE_ZOOM_LEVEL - (log($radius) / log(2)));
    }
}
