<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * Interface FileProxyDelivererInterface
 * @package CIC\Cicbase\Proxy
 */
interface FileProxyDelivererInterface {
    /**
     * Handle the response -- deliver the file or a 404 or whatever you like
     */
    public function respond();

    /**
     * Absolute path to a file
     * @param string $file
     */
    public function setFile($file = '');
}
