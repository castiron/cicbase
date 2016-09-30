<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;
use CIC\Cicbase\Utility\HttpHeaderUtility;

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
        if (!$path) {
            $GLOBALS['TSFE']->pageNotFoundAndExit();
        }

        $mimeType = static::getMimeType($path);
        header("Content-Type: $mimeType");
        if (count($headers)) {
            HttpHeaderUtility::sendHeaders($headers);
        }
        readfile($path);
        exit;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected static function getMimeType($path) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $out = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $out;
    }

}
