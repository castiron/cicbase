<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Utility\HttpHeaderUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Class FalFileProxyDeliverer
 * @package CIC\Cicbase\Proxy\File
 */
class FalFileProxyDeliverer extends FileProxyDeliverer {
    /**
     * @param $path
     * @param array $headers
     */
    protected static function deliverFile($path, $headers = array()) {
        if (!$path) {
            $GLOBALS['TSFE']->pageNotFoundAndExit();
        }

        $file = ResourceFactory::getInstance()->retrieveFileOrFolderObject($path);
        if (!$file) {
            $GLOBALS['TSFE']->pageNotFoundAndExit();
        }

        HttpHeaderUtility::sendHeaders(array_merge(
            $headers, static::fileHeaders($file)
        ));
        readfile($path);
        exit;
    }

    /**
     * @param File $file
     * @return array
     */
    protected static function fileHeaders(File $file) {
        return array(
            'Content-Type: ' . $file->getMimeType(),
            'Content-Length: ' . $file->getSize(),
        );
    }

}
