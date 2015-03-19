<?php

namespace OlcsTest\Service\Data\Search;

use Common\Service\Data\Search\SearchTypeManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Data\Object\Search\Application;
use Olcs\Data\Object\Search\Licence;
use Olcs\Service\Data\Search\SearchType;
use Olcs\Service\NavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchTypeTest
 * @package OlcsTest\Service\Data\Search
 */
class SearchTypeTest extends TestCase
{
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

        $mockNavFactory = m::mock(NavigationFactory::class);
        $mockNavFactory->shouldReceive('getNavigation')
            ->with(m::on($matcher))
            ->andReturn('navigation');

        $sut = new SearchType();
        $sut->setSearchTypeManager($this->getMockSearchTypeManager());
        $sut->setNavigationFactory($mockNavFactory);

        $this->assertEquals('navigation', $sut->getNavigation());

    }

    public function testFetchListOptions()
    {
        $sut = new SearchType();
        $sut->setSearchTypeManager($this->getMockSearchTypeManager());
        $options = $sut->fetchListOptions(null);

        $this->assertCount(2, $options);
    }

    public function testCreateService()
    {
        $mockStm = $this->getMockSearchTypeManager();
        $mockNav = m::mock(NavigationFactory::class);

        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with(SearchTypeManager::class)->andReturn($mockStm);
        $mockSl->shouldReceive('get')->with('NavigationFactory')->andReturn($mockNav);

        $sut = new SearchType();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf(SearchType::class, $service);
        $this->assertSame($mockNav, $service->getNavigationFactory());
        $this->assertSame($mockStm, $service->getSearchTypeManager());
    }
}
