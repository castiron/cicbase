<?php namespace CIC\Cicbase\Proxy\File;

use CIC\Cicbase\Proxy\File\Contracts\FileProxyDenierInterface;
use CIC\Cicbase\Proxy\File\Traits\FileAssociable;
use CIC\Cicbase\Traits\FrontendInstantiating;
use CIC\Cicbase\Utility\HttpHeaderUtility;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FileProxyDenier
 * @package CIC\Cicbase\Proxy
 */
class FileProxyDenier implements FileProxyDenierInterface {

    use FileAssociable;
    use FrontendInstantiating;

    /**
     *
     */
    public function respond() {
        if (TYPO3_MODE !== 'FE') {
            static::initializeFrontend();
        }
        HttpHeaderUtility::noCache();
        static::tsfe()->pageNotFoundAndExit('File not found');
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected static function tsfe() {
        return $GLOBALS['TSFE'];
    }

}
