<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyGatewayInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;

/**
 * Class FileProxyGateway
 * @package CIC\Cicbase\Proxy\File
 */
abstract class FileProxyGateway implements FileProxyGatewayInterface {

    use FileAssociable;

}
