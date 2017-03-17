<?php namespace CIC\Cicbase\Tests\Traits;

use CIC\Cicbase\Traits\Database;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class DatabaseTraitTest
 * @package CIC\Cicbase\Tests\Traits
 */
class DatabaseTraitTest extends UnitTestCase {
    use Database;

    public function testItCreatesWhereClauseFromArray() {
        $this->assertEquals(
            'uid=1 AND something IN(1,2,3)',
            static::buildWhereClause([
                'uid=1',
                'something IN(1,2,3)',
            ])
        );
    }

    public function testItCreatesWhereClauseFromArrayWithExplicitConjunction() {
        $this->assertEquals(
            '(uid=1 AND something IN(1,2,3))',
            static::buildWhereClause([
                'AND' => [
                    'uid=1',
                    'something IN(1,2,3)',
                ],
            ])
        );
    }

    public function testItHandlesMultipleConjunctions() {
        $this->assertEquals(
            '(uid=1 AND something IN(1,2,3)) AND (something < 2323 OR words="dirtman")',
            static::buildWhereClause([
                'AND' => [
                    'uid=1',
                    'something IN(1,2,3)',
                ],
                'OR' => [
                    'something < 2323',
                    'words="dirtman"'
                ]
            ])
        );

        $this->assertEquals(
            '(uid=1 AND something IN(1,2,3)) OR (something < 2323 OR words="dirtman")',
            static::buildWhereClause([
                'AND' => [
                    'uid=1',
                    'something IN(1,2,3)',
                ],
                'OR' => [
                    'something < 2323',
                    'words="dirtman"'
                ]
            ], 'OR')
        );

        $this->assertEquals(
            '((uid=1 AND something IN(1,2,3)) OR (something < 2323 OR words="dirtman" OR (FROM_UNIXTIME(cheese) = 12345678911 AND floppy_levels < 13)))',
            static::buildWhereClause([
                'OR' => [
                    'AND' => [
                        'uid=1',
                        'something IN(1,2,3)',
                    ],
                    'OR' => [
                        'something < 2323',
                        'words="dirtman"',
                        'AND' => [
                            'FROM_UNIXTIME(cheese) = 12345678911',
                            'floppy_levels < 13',
                        ]
                    ]
                ]
            ])
        );
    }

}
