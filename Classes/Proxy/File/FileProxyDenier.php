<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyDenierInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;

/**
 * Class FileProxyDenier
 * @package CIC\Cicbase\Proxy
 */
class FileProxyDenier implements FileProxyDenierInterface {

    use FileAssociable;

    /**
     *
     */
    public function respond() {
        die('TODO: throw a denier 404');
        // TODO: Throw a TYPO3 404 with proper reason (group access needed)
    }

}
