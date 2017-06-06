<?php namespace CIC\Cicbase\Tests\Traits;

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class DatabaseTraitTest
 * @package CIC\Cicbase\Tests\Traits
 */
class DatabaseTraitTest extends UnitTestCase {
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Repository|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $repository;

    /**
     * @test
     */
    public function testItProvidesGetters() {
        $domainObject = $this->generateDummyClass();
        $domainObject->foo = 'something';
        $this->assertEquals('something', $domainObject->getFoo());
    }

    /**
     * @test
     */
    public function testItProvidesSetters() {
        $domainObject = $this->generateDummyClass();
        $domainObject->setFoo('something');
        $this->assertEquals('something', $domainObject->foo);
    }

    /**
     * @test
     */
    public function testExistingGettersAreUsed() {
        $domainObject = $this->generateDummyClass();
        $domainObject->ronald = 'something';
        $this->assertEquals('something ok', $domainObject->getRonald());
    }

    /**
     * @test
     */
    public function testExistingSettersAreUsed() {
        $domainObject = $this->generateDummyClass();
        $domainObject->setBaz('something');
        $this->assertEquals('something ok', $domainObject->baz);
    }

    /**
     * @test
     */
    public function testInvalidGetterThrowsException() {
        $domainObject = $this->generateDummyClass();
        try {
            $domainObject->getFlopula();
            $this->fail('Expected exception');
        } catch (\Exception $ex) {
            $this->assertEquals($ex->getMessage(), 'Call to undefined method getFlopula');
        }
    }

    /**
     * @test
     */
    public function testInvalidSetterThrowsException() {
        $domainObject = $this->generateDummyClass();
        try {
            $domainObject->setFlopula();
            $this->fail('Expected exception');
        } catch (\Exception $ex) {
            $this->assertEquals($ex->getMessage(), 'Call to undefined method setFlopula');
        }
    }

    /**
     * @return AbstractEntity
     */
    protected function generateDummyClass() {
        $domainObjectName = $this->getUniqueId('DomainObject_');
        $domainObjectNameWithNS = __NAMESPACE__ . '\\' . $domainObjectName;
        eval('namespace ' . __NAMESPACE__ . '; class ' . $domainObjectName . ' extends \\TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity {
            use \CIC\Cicbase\Traits\ExtbaseModelAccessible;
            public $foo;
            public $bar;
            public $baz;
            public $ronald;
            
            public function setBaz($val) {
                $this->baz = $val . \' ok\';
            }

            public function getBaz() {
                return $this->baz;
            }
             
            public function setRonald($val) {
                $this->ronald = $val;
            }

            public function getRonald() {
                return $this->ronald . \' ok\';
            } 
        }');

        return new $domainObjectNameWithNS();
    }
}
