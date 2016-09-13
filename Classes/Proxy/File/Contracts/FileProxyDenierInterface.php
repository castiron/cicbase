<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * Interface FileProxyDenierInterface
 * @package CIC\Cicbase\Proxy
 */
interface FileProxyDenierInterface {
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
