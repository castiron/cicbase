<?php namespace CIC\Cicbase\Tests\Utility;

use CIC\Cicbase\Utility\Url;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class UrlTest
 * @package CIC\Cicbase\Tests\Utility
 */
class UrlTest extends UnitTestCase {
    /**
     * @var string
     */
    var $baseUrl = '//something.tld/ok/this/is/a/url';

    /**
     * @test
     */
    public function testItDoesntStripAnythingWhenNoParamsMatch() {
        $baseUrl = $this->baseUrl;
        foreach ([
             $baseUrl,
             "$baseUrl/",
             '//wahtever',
         ] as $url) {
            $this->assertEquals($url, Url::stripQueryStringParameters($url, ['charlie_brown', 'dirtman']));
        }

        foreach ([
             "$baseUrl?something=false&spaghetti=true",
             "$baseUrl/?something=false&spaghetti=true",
             "$baseUrl/?something=false&spaghetti=true&hands=&cheeseburger=backpack",
             '//wahtever?something=false&spaghetti=true',
         ] as $url) {
            $this->assertEquals($url, Url::stripQueryStringParameters($url, ['charlie_brown', 'dirtman']));
        }
    }

    /**
     * @test
     */
    public function testItStripsParamsFromUrls() {
        $baseUrl = $this->baseUrl;
        $someParams = [
            'autoPlay=true&mute=false',
            'autoPlay=true&mute=true',
            'mute=true&autoPlay=true',
        ];
        foreach ($someParams as $someParam) {
            foreach ([
                 $baseUrl,
                 "$baseUrl/",
                 '//wahtever',
             ] as $url) {
                $this->assertEquals($url, Url::stripQueryStringParameters($url . "?$someParam", ['autoPlay', 'mute']));
                $this->assertEquals($url, Url::stripQueryStringParameters($url . "?$someParam", ['mute', 'autoPlay']));

                foreach ([
                     "something=false&spaghetti=true",
                     "something=false&spaghetti=true&hands=&cheeseburger=backpack",
                 ] as $params) {
                    $this->assertEquals($url . '?' . $params, Url::stripQueryStringParameters($url . "?$params&$someParam", ['mute', 'autoPlay']));
                    $this->assertEquals($url . '?' . $params, Url::stripQueryStringParameters($url . "?$someParam&$params", ['mute', 'autoPlay']));
                }
            }
        }
    }

    public function testItAddsParamsToUrls() {
        $baseUrl = $this->baseUrl;
        foreach ([
             $baseUrl,
             "$baseUrl/",
             '//wahtever',
         ] as $url) {
            $this->assertEquals($url . '?myparam=something', Url::addQueryStringParameters($url . '?myparam=something', ['myparam' => 'something']));
            $this->assertEquals($url, Url::addQueryStringParameters($url, []));
        }

        $this->assertEquals(
            $baseUrl . '?myparam=worf_man123&another_thing=' . urlencode('Dirt Man Ok?'),
            Url::addQueryStringParameters($baseUrl . '?myparam=something', ['myparam' => 'worf_man123', 'another_thing' => 'Dirt Man Ok?'])
        );
    }
}
