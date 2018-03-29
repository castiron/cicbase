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

        static::readfile($path);

        exit;
    }

    /**
     * RE: huge files: PHP handles huge files with `readfile` no problemo, but if output buffering is on you can run
     * into probs.
     *
     * This wrapper makes sure there is no output buffering going on (which would cause the entire delivered file
     * to be loaded into the buffer, potentially surpassing memory_limit).
     *
     * @param $path
     */
    protected static function readfile($path)
    {
        static::clearBuffer();
        readfile($path);
    }

    /**
     *
     */
    protected static function clearBuffer() {
        /**
         * We use a static level rather than calling ob_get_level() each time (which _should_ decrement)
         * for safety's sake.
         */
        $depth = ob_get_level();
        while ($depth) {
            ob_end_clean();
            $depth--;
        }
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
            'Content-Length: ' . filesize($file->getForLocalProcessing(false)),
        );
    }



}
