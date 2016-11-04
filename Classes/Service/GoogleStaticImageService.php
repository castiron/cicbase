<?php namespace CIC\Cicbase\Service;
use CIC\Cicbase\Domain\Model\GoogleMaps\StaticImage;
use CIC\Cicbase\Traits\ExtbaseInstantiable;
use CIC\Cicbase\Utility\Arr;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\FileHandlingUtility;

/**
 * TODO: Set up garbage collection
 *
 * Class GoogleStaticImageService
 * @package CIC\Cicbase\Service
 */
class GoogleStaticImageService {
    use ExtbaseInstantiable;

    const CACHE_KEY = 'cicbase_cache';
    const CACHE_PREFIX = 'google_static_img_';

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

        /**
         * Set up the storage folder
         */
        $this->storageFolder = rtrim($args['storageFolder'] ?: static::defaultStorageFolder(), '/');
        if (!GeneralUtility::isAbsPath($this->storageFolder)) {
            throw new Exception('Storage folder must be an absolute path');
        }
        if (!is_dir($this->storageFolder)) {
            GeneralUtility::mkdir_deep($this->storageFolder);
        }
    }

    /**
     * @param $url
     * @return string
     */
    protected static function cacheKey($url){
        return static::CACHE_PREFIX . GeneralUtility::shortMD5($url);
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
        /**
         * Get the key on there, extending the default key if present
         */
        $params = array_merge(array('key' => $this->apiKey), $params);
        return $this->fetchAndCacheImage(
            StaticImage::get($params)->getUrl()
        );
    }

    /**
     * @param $url
     * @return string
     */
    protected function fetchAndCacheImage($url) {
        /**
         * Determine the cache key
         */
        $key = static::cacheKey($url);

        /**
         * Get the cache
         */
        $cache = $this->getCache();
        $existing = $cache->get($key);
        if ($existing && file_exists(PATH_site . $existing)) {
            return $existing;
        }

        /**
         * Save the image from ye remote service
         */
        $newFile = $this->storageFolder . DIRECTORY_SEPARATOR . static::urlToFileName($url);
        if (static::saveImageFromRemote($url, $newFile)) {
            /**
             * Set the URL in the cache
             */
            $cache->set($key, $newFile);

            /**
             * Return the path to the file
             */
            return static::absolutePathToRelativePath($newFile);
        }

        return '';
    }

    /**
     * @param $path
     * @return string
     */
    protected static function absolutePathToRelativePath($path) {
        $temp = GeneralUtility::removePrefixPathFromList(array($path), PATH_site);
        return $temp[0];
    }

    /**
     * @param $url
     * @return string
     */
    protected static function urlToFileName($url) {
        return 'map_' . GeneralUtility::shortMD5($url, 16) . '.png';
    }

    /**
     * @param $url
     * @param $newFile
     * @return mixed
     */
    protected static function saveImageFromRemote($url, $newFile) {
        $ch = curl_init($url);
        $fp = fopen($newFile, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $newFile;
    }


    /**
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected function getCache() {
        return $this->cacheManger->getCache(static::CACHE_KEY);
    }
}
