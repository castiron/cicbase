<?php namespace CIC\Cicbase\Tests\Domain\Model\GoogleMaps;

use CIC\Cicbase\Domain\Model\GoogleMaps\StaticImageMarker;
use CIC\Cicbase\Domain\Model\LatLng;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class StaticImageMarkerTest
 * @package CIC\Cicbase\Tests\Domain\Model\GoogleMaps
 */
class StaticImageMarkerTest extends UnitTestCase {
    /**
     *
     */
    public function testItProvidesStringUrlParameter() {
        $marker = StaticImageMarker::get(array(
            'color' => 'red',
            'size' => 'mid',
            'label' => 'My Label',
            'locations' => array(
                LatLng::get(array('lat' => 80,  'lng' => -15.12)),
                LatLng::get(array('lat' => -10, 'lng' => -20)),
                LatLng::get(array('lat' => 20,  'lng' => -30)),
            ),
        ));
        $this->assertEquals('color:red%7Csize:mid%7Clabel:My+Label%7C80,-15.12%7C-10,-20%7C20,-30', $marker->toStaticMapsUrlParam());
    }

    public function testItOnlyProvidesExistingParameters() {
        $marker = StaticImageMarker::get(array(
            'color' => 'red',
        ));
        $this->assertEquals('color:red', $marker->toStaticMapsUrlParam());
    }

    public function testItCanProvideEmptyParameters() {
        $marker = StaticImageMarker::get();
        $this->assertEquals('', $marker->toStaticMapsUrlParam());
    }

    public function testItProvidesLocationData() {
        $marker = StaticImageMarker::get(array(
            'locations' => array(
                LatLng::get(array('lat' => 80,  'lng' => -15.12)),
                LatLng::get(array('lat' => -10, 'lng' => -20)),
                LatLng::get(array('lat' => 20,  'lng' => -30)),
            ),
        ));
        $this->assertEquals('80,-15.12%7C-10,-20%7C20,-30', $marker->toStaticMapsUrlParam());
    }
}
