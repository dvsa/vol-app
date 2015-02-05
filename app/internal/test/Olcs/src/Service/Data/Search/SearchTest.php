<?php

namespace OlcsTest\Service\Data\Search;

use Olcs\Data\Object\Search\Application;
use Olcs\Data\Object\Search\Licence;
use Olcs\Service\Data\Search\Search;
use Zend\Stdlib\ArrayObject;
use Mockery as m;

/**
 * Class SearchTest
 * @package OlcsTest\Service\Data\Search
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetLimit
     * @param $query
     * @param $expected
     */
    public function testGetLimit($query, $expected)
    {
        $sut = new Search();
        $sut->setQuery($query);

        $this->assertEquals($expected, $sut->getLimit());
    }

    public function provideGetLimit()
    {
        $stubQuery = new \ArrayObject();
        $stubQuery->limit = 15;

        return [
            [$stubQuery, 15],
            [new ArrayObject(), 10],
            [null, 10]
        ];
    }

    /**
     * @dataProvider provideGetPage
     * @param $query
     * @param $expected
     */
    public function testGetPage($query, $expected)
    {
        $sut = new Search();
        $sut->setQuery($query);

        $this->assertEquals($expected, $sut->getPage());
    }

    public function provideGetPage()
    {
        $stubQuery = new \ArrayObject();
        $stubQuery->page = 3;

        return [
            [$stubQuery, 3],
            [new ArrayObject(), 1],
            [null, 1]
        ];
    }

    protected function getMockSearchTypeManager()
    {
        $servicesArray = [
            'factories' => [
                'licence'
            ],
            'invokableClasses' => [
                'application'
            ]
        ];

        $mockStm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockStm->shouldReceive('getRegisteredServices')->andReturn($servicesArray);
        $mockStm->shouldReceive('get')->with('application')->andReturn(new Application());
        $mockStm->shouldReceive('get')->with('licence')->andReturn(new Licence());

        return $mockStm;
    }

    public function testGetNavigation()
    {
        $matcher = function ($item) {
            return (is_array($item) && count($item) == 2);
        };

        $mockNavFactory = m::mock('Olcs\Service\NavigationFactory');
        $mockNavFactory->shouldReceive('getNavigation')
            ->with(m::on($matcher))
            ->andReturn('navigation');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
               ->with('Olcs\Service\Data\Search\SearchTypeManager')
               ->andReturn($this->getMockSearchTypeManager());

        $mockSl->shouldReceive('get')->with('NavigationFactory')->andReturn($mockNavFactory);
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();

        $sut = new Search();
        $sut->setServiceLocator($mockSl);

        $this->assertEquals('navigation', $sut->getNavigation());

    }

    public function testFetchListOptions()
    {
        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
            ->with('Olcs\Service\Data\Search\SearchTypeManager')
            ->andReturn($this->getMockSearchTypeManager());

        $sut = new Search();
        $sut->setServiceLocator($mockSl);
        $options = $sut->fetchListOptions('');

        $this->assertCount(2, $options);
    }

    public function testFetchResults()
    {
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->with('/search/index?limit=10&page=1')->andReturn('result');

        $sut = new Search();
        $sut->setRestClient($mockRestClient);
        $sut->setSearch('search');
        $sut->setIndex('index');

        $mockSearchTypeManager = $this->getMockSearchTypeManager();
        $mockDataClass = m::mock('StdClass');
        $mockDataClass->shouldReceive('getSearchIndices')->andReturn('index');
        $mockSearchTypeManager->shouldReceive('get')->with($sut->getIndex())->andReturn($mockDataClass);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
            ->with('Olcs\Service\Data\Search\SearchTypeManager')
            ->andReturn($mockSearchTypeManager);

        $sut->setServiceLocator($mockSl);

        $this->assertEquals('result', $sut->fetchResults());

    }

    public function testFetchResultsWithSpace()
    {
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->with('/search+space/index?limit=10&page=1')->andReturn('result');

        $sut = new Search();
        $sut->setRestClient($mockRestClient);
        $sut->setSearch('search space');
        $sut->setIndex('index');

        $mockSearchTypeManager = $this->getMockSearchTypeManager();
        $mockDataClass = m::mock('StdClass');
        $mockDataClass->shouldReceive('getSearchIndices')->andReturn('index');
        $mockSearchTypeManager->shouldReceive('get')->with($sut->getIndex())->andReturn($mockDataClass);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
            ->with('Olcs\Service\Data\Search\SearchTypeManager')
            ->andReturn($mockSearchTypeManager);

        $sut->setServiceLocator($mockSl);

        $this->assertEquals('result', $sut->fetchResults());

    }

    public function testFetchResultsTable()
    {
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->andReturn('table');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
            ->with('Olcs\Service\Data\Search\SearchTypeManager')
            ->andReturn($this->getMockSearchTypeManager());

        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);

        $sut = new Search();
        $sut->setData('results', ['results']);
        $sut->setServiceLocator($mockSl);
        $sut->setIndex('application');

        $this->assertEquals('table', $sut->fetchResultsTable());
    }
}
