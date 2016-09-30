<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * Interface FileProxyDelivererInterface
 * @package CIC\Cicbase\Proxy
 */
interface FileProxyDelivererInterface {
    /**
     * Handle the response -- deliver the file or a 404 or whatever you like
     * @param array $headers Addition HTTP headers to send
     */
    public function respond($headers);

    /**
     * Absolute path to a file
     * @param string $file
     */
    public function setFile($file = '');
}
