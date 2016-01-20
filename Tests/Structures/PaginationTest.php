<?php

namespace CIC\Cicbase\Tests\Structures;

use CIC\Cicbase\Structures\Pagination;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class PaginationTest extends UnitTestCase
{
    public function testSinglePage()
    {
        $res = self::pages(1, 10, 1);
        $this->assertEquals(array(1), $res);
    }

    public function testFewPages()
    {
        $res = self::pages(31, 10, 2);
        $this->assertEquals(array(1,2,3,4), $res);
    }

    public function testCurrentInFirstPages()
    {
        $res = self::pages(61, 10, 3);
        $this->assertEquals(array(1,2,3,4,5,6,7), $res);
    }

    public function testCurrentInFirstPagesWithMorePages()
    {
        $res = self::pages(81, 10, 3);
        $this->assertEquals(array(1,2,3,4,5,'...',7,8,9), $res);
    }

    public function testCurrentInLastPages()
    {
        $res = self::pages(61, 10, 5);
        $this->assertEquals(array(1,2,3,4,5,6,7), $res);
    }

    public function testCurrentInLastPagesWithMorePages()
    {
        $res = self::pages(81, 10, 7);
        $this->assertEquals(array(1,2,3,'...',5,6,7,8,9), $res);
    }

    public function testCurrentInMiddlePages()
    {
        $res = self::pages(101, 10, 6);
        $this->assertEquals(array(1,2,3, 4,5,6,7,8, 9,10,11), $res);
    }

    public function testCurrentInMiddleWithMorePages()
    {
        $res = self::pages(151, 10, 8);
        $expected = array(1,2,3,'...',6,7,8,9,10,'...',14,15,16);
        $this->assertEquals($expected, $res);
    }

    protected static function pages($total, $size, $current)
    {
        $p = new Pagination($total, $size, $current);
        return $p->makeSimpleArray();
    }

}