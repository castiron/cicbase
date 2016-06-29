<?php namespace CIC\Cicbase\Utility;

/**
 * Class HttpHeaderUtility
 * @package CIC\Cicbase\Utility
 */
class HttpHeaderUtility {
    public static function noCache() {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}
