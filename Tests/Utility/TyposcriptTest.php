<?php

namespace CIC\Cicbase\Tests\Utility;

use CIC\Cicbase\Utility\Typoscript;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TyposcriptTest
 * @package CIC\Cicbase\Tests\Utility
 */
class TyposcriptTest extends UnitTestCase {
    /**
     *
     */
    public function testItGetsTyposcriptCobjTypeAndConfigByPath() {
        $config = [
            'textThing' => 'TEXT',
            'textThing.' => [
                'value' => 'things',
                'wrap.' => [
                    'field' => 'dirt_nap'
                ]
            ]
        ];
        $input = [
            'lib.' => [
                'something' => 'COA',
                'something.' => [
                    '10' => 'TEXT',
                    '10.' => $config,
                ],
            ]
        ];

        $this->assertEquals(['TEXT', $config], Typoscript::getConfigForPath('lib.something.10', $input));
        $this->assertEquals([], Typoscript::getConfigForPath('lib.nonExisting.thing', $input));
    }

    public function testItGetsTyposcriptConfigByPath() {
        $config = [
            'textThing' => 'TEXT',
            'textThing.' => [
                'value' => 'things',
                'wrap.' => [
                    'field' => 'dirt_nap'
                ]
            ]
        ];
        $input = [
            'lib.' => [
                'something.' => [
                    '10.' => $config,
                ],
            ]
        ];
        $this->assertEquals(['', $config], Typoscript::getConfigForPath('lib.something.10', $input));
    }


}
