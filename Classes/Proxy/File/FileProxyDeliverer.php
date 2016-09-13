<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;

/**
 * Class FileProxyDeliverer
 * @package CIC\Cicbase\Proxy
 */
class FileProxyDeliverer implements FileProxyDelivererInterface {

    use FileAssociable;

    /**
     *
     */
    public function respond() {
        static::deliverFile($this->file);
    }

    /**
     * @param $path string
     */
    protected static function deliverFile($path) {
        if (!$path) {
            $GLOBALS['TSFE']->pageNotFoundAndExit();
        }

        $mimeType = static::getMimeType($path);
        header("Content-Type: $mimeType");
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
