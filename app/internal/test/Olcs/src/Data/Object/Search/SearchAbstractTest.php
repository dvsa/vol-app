<?php

namespace OlcsTest\Data\Object\Search;

/**
 * Class SearchAbstractTest
 * @package OlcsTest\Data\Object\Search
 */
abstract class SearchAbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $class = '';

    public function testGetTableConfig()
    {
        /** @var \Olcs\Data\Object\Search\SearchAbstract $sut */
        $sut = new $this->class;
        $this->assertInternalType('array', $sut->getTableConfig());
        $this->assertArrayHasKey('variables', $sut->getTableConfig());
        $this->assertArrayHasKey('settings', $sut->getTableConfig());
        $this->assertArrayHasKey('attributes', $sut->getTableConfig());
        $this->assertArrayHasKey('columns', $sut->getTableConfig());
    }

    public function testGetNavigation()
    {
        /** @var \Olcs\Data\Object\Search\SearchAbstract $sut */
        $sut = new $this->class;
        $this->assertInternalType('array', $sut->getNavigation());
        $this->assertArrayHasKey('label', $sut->getNavigation());
        $this->assertArrayHasKey('route', $sut->getNavigation());
        $this->assertArrayHasKey('params', $sut->getNavigation());
    }

    public function testGetTitle()
    {
        /** @var \Olcs\Data\Object\Search\SearchAbstract $sut */
        $sut = new $this->class;
        $this->assertInternalType('string', $sut->getTitle());
    }

    public function testGetKey()
    {
        /** @var \Olcs\Data\Object\Search\SearchAbstract $sut */
        $sut = new $this->class;
        $this->assertInternalType('string', $sut->getKey());
    }

    public function testGetSearchIndices()
    {
        /** @var \Olcs\Data\Object\Search\SearchAbstract $sut */
        $sut = new $this->class;
        $this->assertInternalType('string', $sut->getSearchIndices());
    }
}
