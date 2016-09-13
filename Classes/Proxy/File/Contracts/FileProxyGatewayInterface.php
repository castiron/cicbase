<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * This is for a class that would determine whether a given file should be accessible on the current request
 *
 * Interface FileProxyGatewayInterface
 * @package CIC\Cicbase\Proxy\File\Contracts
 */
interface FileProxyGatewayInterface {
    /**
     * @return bool
     */
    public function isAccessible();

    /**
     * Absolute path to a file
     * @param string $file
     */
    public function setFile($file = '');
}
