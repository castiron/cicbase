<?php namespace CIC\Cicbase\Tests\Service;

use CIC\Cicbase\Service\GoogleStaticImageService;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        GeneralUtility::rmdir(static::storageFolderPath(), true);
    }

    /**
     *
     */
    public function testItReturnsAUri() {
        $uri = $this->staticImageService->fetchImage(array(
            'size' => '100x100',
        ));
        $this->assertStringStartsWith(static::TEST_STORAGE_FOLDER . '/map_', $uri);
        $this->assertStringEndsWith('.png', $uri);
    }

    /**
     *
     */
    public function testItCreatesAFile() {
        $uri = $this->staticImageService->fetchImage(array(
            'size' => '100x100',
        ));
        $this->assertFileExists(PATH_site . DIRECTORY_SEPARATOR . $uri);
    }
}
