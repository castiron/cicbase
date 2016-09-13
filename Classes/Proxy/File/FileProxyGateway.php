<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyGatewayInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;

/**
 * Class FileProxyGateway
 * @package CIC\Cicbase\Proxy\File
 */
class FileProxyGateway implements FileProxyGatewayInterface {

    use FileAssociable;

    /**
     * Override this in your child class
     *
     * @return bool
     */
    public function isAccessible() {
        return true;
    }

}
