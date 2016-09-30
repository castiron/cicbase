<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use CIC\Cicbase\Proxy\File\Contracts\FileProxyInterface;
use CIC\Cicbase\Utility\HttpHeaderUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FileProxy
 * @package CIC\Qscresources\Proxy
 */
class FileProxy implements FileProxyInterface {

    /**
     * This means that to get one of these objies you should do something like:
     *  FileProxy::get(array('file' => '/my/file/path.pdf'));
     * Then all the dependency injection in here will work ;)
     */
    use ExtbaseInstantiable;

    /**
     * Override what is injected here with Typoscript if you want
     *
     * @var \CIC\Cicbase\Proxy\File\Contracts\FileProxyDenierInterface
     * @inject
     */
    var $fileDenier;

    /**
     * Override what is injected here with Typoscript if you want
     *
     * @var \CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface
     * @inject
     */
    var $fileDeliverer;

    /**
     * Override what is injected here with Typoscript if you want
     *
     * @var \CIC\Cicbase\Proxy\File\Contracts\FileProxyGatewayInterface
     * @inject
     */
    var $fileGateway;

    /**
     * @var string
     */
    protected $file = '';

    /**
     *
     */
    public function initializeObject() {
        $this->fileGateway->setFile($this->file);
        $this->fileDeliverer->setFile($this->file);
        $this->fileDenier->setFile($this->file);
    }

    /**
     * FileProxy constructor.
     * @param array $args
     */
    public function __construct($args = array()) {
        $this->file = $args['file'];
    }

    /**
     * Hanldle the request by delivering the file or denying access
     */
    public function proxy() {
        if (!file_exists($this->file)) {
            static::getTsfe()->pageNotFoundAndExit('File not found');
        }

        if ($this->fileGateway->isAccessible()) {
            $headers = array();
            if ($this->fileGateway->isPublic()) {
                $headers[] = 'Cache-Control: public';
            } else {
                $headers = array_merge($headers, HttpHeaderUtility::noCacheHeaders());
            }
            $this->fileDeliverer->respond($headers);
            die;
        }

        $this->fileDenier->respond();
        die;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected static function getTsfe() {
        return $GLOBALS['TSFE'];
    }
}
