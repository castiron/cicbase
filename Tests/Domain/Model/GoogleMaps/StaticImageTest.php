<?php namespace CIC\Cicbase\Tests\Domain\Model\GoogleMaps;

use CIC\Cicbase\Domain\Model\GoogleMaps\StaticImage;
use CIC\Cicbase\Domain\Model\GoogleMaps\StaticImageMarker;
use CIC\Cicbase\Domain\Model\GoogleMapsStaticImage;
use CIC\Cicbase\Domain\Model\LatLng;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class StaticImageTest
 * @package CIC\Cicbase\Tests\Domain\Model\GoogleMaps
 */
class StaticImageTest extends UnitTestCase {
    const API_URL = 'https://maps.googleapis.com/maps/api/staticmap';
    const API_KEY = '111aasssdiiennasddbuyeyyhhsd';

    /**
     * @var StaticImageMarker
     */
    var $dummyMarker;

    public function setUp() {
        $this->dummyMarker = StaticImageMarker::get(array(
            'color' => 'red',
            'size' => 'mid',
            'label' => 'My Label',
            'locations' => array(
                LatLng::get(array('lat' => 80,  'lng' => -15.12)),
                LatLng::get(array('lat' => -10, 'lng' => -20)),
                LatLng::get(array('lat' => 20,  'lng' => -30)),
            ),
        ));
    }

    /**
     * @test
     */
    public function testItThrowsErrorWithoutApiKey() {
        $this->setExpectedException('TYPO3\CMS\Core\Error\Exception', 'Please provide a Google Maps API key.');
        StaticImage::get();
    }

    /**
     * @test
     */
    public function testItRequiresSizeParameter() {
        $this->setExpectedException('TYPO3\CMS\Core\Error\Exception', 'You must provide a size parameter like "200x300"');
        StaticImage::get(array('key' => static::API_KEY));
    }

    /**
     * @test
     */
    public function testItGeneratesAFullUrl() {
        $key = 'adsfadfasdfasdfsadf';
        $staticImage = StaticImage::get(array(
            'key' => $key,
            'size' => '200x300',
        ));
        $this->assertEquals(static::API_URL . '?key=' . $key . '&size=200x300', $staticImage->getUrl());
    }

    /**
     * @test
     */
    public function testUrlDoesNotContainUnwantedParams() {
        $key = 'adsfadfasdfasdfsadf';
        $staticImage = StaticImage::get(array(
            'key' => $key,
            'size' => '200x300',
            'leg' => 'whatever you guys',
            0 => 'this is wrong!',
            1 => 'n on ono no no NEVER'
        ));
        $this->assertEquals(static::API_URL . '?key=' . $key . '&size=200x300', $staticImage->getUrl());
    }

    /**
     * @test
     */
    public function testUrlIncludesMarkerData() {
        $staticImage = StaticImage::get(array(
            'key' => static::API_KEY,
            'size' => '200x300',
            'markers' => array(
                $this->dummyMarker,
            ),
        ));

        $this->assertEquals(static::API_URL . '?key=' . static::API_KEY . '&size=200x300'
            . '&markers=' . $this->dummyMarker->toStaticMapsUrlParam(),
        $staticImage->getUrl());
    }

    /**
     * @test
     */
    public function testItAddsASingleMarker() {
        $staticImage = StaticImage::get(array(
            'key' => static::API_KEY,
            'size' => '200x300',
            'markers' => array(
                $this->dummyMarker,
            ),
        ));

        $staticImage->addMarker($this->dummyMarker);
        $this->assertEquals(static::API_URL . '?key=' . static::API_KEY . '&size=200x300'
            . '&markers=' . $this->dummyMarker->toStaticMapsUrlParam()
            . '&markers=' . $this->dummyMarker->toStaticMapsUrlParam(),
        $staticImage->getUrl());
    }
}

