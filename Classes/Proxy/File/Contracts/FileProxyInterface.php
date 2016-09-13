<?php namespace CIC\Cicbase\Proxy\File\Contracts;

/**
 * Interface FileProxyInterface
 * @package CIC\Cicbase\Proxy
 */
interface FileProxyInterface {
    /**
     * Either deliver or deny via the handlers
     */
    public function proxy();
}
