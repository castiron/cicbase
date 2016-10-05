<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;
use CIC\Cicbase\Utility\HttpHeaderUtility;
use CIC\Cicbase\Utility\MimeTypeUtility;

/**
 * Class FileProxyDeliverer
 * @package CIC\Cicbase\Proxy
 */
class FileProxyDeliverer implements FileProxyDelivererInterface {

    use FileAssociable;

    /**
     * @param array $headers
     */
    public function respond($headers) {
        static::deliverFile($this->file, $headers);
    }

    /**
     * @param $path
     * @param array $headers
     */
    protected static function deliverFile($path, $headers = array()) {
        /**
         * Thrash if no file
         */
        if (!$path) {
            $GLOBALS['TSFE']->pageNotFoundAndExit();
        }

        /**
         * Send MIME type
         */
        $mimeType = MimeTypeUtility::mimeFromPath($path);
        header("Content-Type: $mimeType");

        /**
         * Send the headers
         */
        if (count($headers)) {
            HttpHeaderUtility::sendHeaders($headers);
        }

        /**
         * Send the file data
         */
        readfile($path);
        exit;
    }
}
