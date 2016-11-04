<?php namespace CIC\Cicbase\Service;
use CIC\Cicbase\Domain\Model\GoogleMaps\StaticImage;
use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Error\Exception;

/**
 * TODO: fetch image to file, return it's web URI
 * TODO: Use TYPO3 cache to store file path info for each request. If there's no cached info, rewrite the image.
 *
 * Class GoogleStaticImageService
 * @package CIC\Cicbase\Service
 */
class GoogleStaticImageService {
    use ExtbaseInstantiable;

    const CACHE_KEY = 'tx_cicbase_google_static_images';

    /**
     * One year
     * @var int
     */
    protected $cacheLifetime = 31536000;

    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     * @inject
     */
    protected $cacheManger;

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $storageFolder;

    /**
     * GoogleStaticImageService constructor.
     * @param $args
     * @throws Exception
     */
    public function __construct($args) {
        /**
         * Can specify apiKey for convenience
         */
        if ($args['apiKey']) {
            $this->apiKey = $args['apiKey'];
        }
        $this->storageFolder = $args['storageFolder'] ?: static::defaultStorageFolder();
    }

    /**
     * @return string
     */
    protected static function defaultStorageFolder() {
        return PATH_site . 'typo3temp/google_maps_images';
    }

    /**
     * Required $params are "key" and "size". See StaticImage
     *
     * @param array $params
     * @return string The URL of a local copy of the image
     */
    public function fetchImage(array $params) {
        $params = array_merge(array('key' => $this->apiKey), $params);
        return $this->fetchAndCacheImage(
            StaticImage::get($params)->getUrl()
        );
    }

    /**
     * TODO: write this
     * @param $url
     * @return string
     */
    protected function fetchAndCacheImage($url) {
        return $url;
    }



    /**
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected function getCache() {
        return $this->cacheManger->getCache(static::CACHE_KEY);
    }
}
