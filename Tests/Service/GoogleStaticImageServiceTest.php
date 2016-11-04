<?php namespace CIC\Cicbase\Tests\Service;

use CIC\Cicbase\Service\GoogleStaticImageService;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class GoogleStaticImageServiceTest
 * @package CIC\Cicbase\Tests\Service
 */
class GoogleStaticImageServiceTest extends UnitTestCase {
    const TEST_STORAGE_FOLDER = 'typo3temp/__static_image_fixtures';

    /**
     * @var GoogleStaticImageService
     */
    var $staticImageService;

    /**
     * @return string
     */
    protected static function storageFolderPath() {
        return PATH_site . static::TEST_STORAGE_FOLDER;
    }

    public function setUp() {
        $storageFolder = static::storageFolderPath();

        /**
         * Make a temp cache dir
         */
        mkdir($storageFolder, 0777, true);

        /**
         * Get the basic image service
         */
        $this->staticImageService = GoogleStaticImageService::get(array(
            'storageFolder' => $storageFolder,
            /**
             * TODO: Use an env var for this
             */
            'apiKey' => 'AIzaSyACbXmjntXUE_gpvloSbu4u1FIw2p09Ees',
        ));
    }

    /**
     *
     */
    public function tearDown() {
        rmdir(static::storageFolderPath());
    }

    /**
     *
     */
    public function testItCachesAnImage() {
        $url = $this->staticImageService->fetchImage(array(
            'size' => '100x100',
        ));
        $this->assertEquals($url, 'https://maps.googleapis.com/maps/api/staticmap?key=AIzaSyACbXmjntXUE_gpvloSbu4u1FIw2p09Ees&size=100x100');
    }
}
