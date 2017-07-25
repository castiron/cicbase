<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Utility\HttpHeaderUtility;
use CIC\Cicbase\Utility\MimeTypeUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
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
     * Use the FAL mime type, but if it looks generic, try to obtain the mime type from the path
     *
     * @param FileInterface $file
     * @return array|string
     */
    protected static function getFileMimeWithFallback(FileInterface $file) {
        $mimeType = $file->getMimeType();
        if (!$mimeType || $mimeType === 'text/plain') {
            return MimeTypeUtility::mimeFromPath($file->getPublicUrl()) ?: 'text/plain';
        }

        return $mimeType;
    }

    /**
     * @param FileInterface $file
     * @return array
     */
    protected static function fileHeaders(FileInterface $file) {
        return array(
            'Content-Type: ' . static::getFileMimeWithFallback($file),
            'Content-Length: ' . $file->getSize(),
        );
    }

}
