<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * This is for a class that would determine whether a given file should be accessible on the current request
 *
 * Interface FileProxyGatewayInterface
 * @package CIC\Cicbase\Proxy\File\Contracts
 */
interface FileProxyGatewayInterface {
    /**
     * Does the current session allow access to the file?
     * @return bool
     */
    public function isAccessible();

    /**
     * Sometimes you want to know whether a particular file is publicly available.
     * For example, when you're deciding what HTTP caching headers to send.
     *
     * @return bool
     */
    public function isPublic();

    /**
     * Absolute path to a file
     * @param string $file
     */
    public function setFile($file = '');
}
