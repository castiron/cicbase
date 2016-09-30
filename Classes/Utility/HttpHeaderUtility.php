<?php namespace CIC\Cicbase\Utility;

/**
 * Class HttpHeaderUtility
 * @package CIC\Cicbase\Utility
 */
class HttpHeaderUtility {
    /**
     * Send strong no-cache headers
     */
    public static function noCache() {
        static::sendHeaders(static::noCacheHeaders());
    }

    /**
     * @param $headers
     * @param bool $overwrite
     */
    public static function sendHeaders($headers, $overwrite = true) {
        foreach ($headers as $header) {
            header($header, $overwrite);
        }
    }

    /**
     * @return array
     */
    public static function noCacheHeaders() {
        return array(
            'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
            'Pragma: no-cache',
        );
    }
}
