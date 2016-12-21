<?php

namespace OlcsTest\Form\Element;

use Common\Service\Data\Search\Search as SearchDataService;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Element\SearchOrderFieldsetFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SearchOrderFieldsetFactory Test
 */
class SearchOrderFieldsetFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $options = [
            'name' => 'element_name'
        ];

        $service = m::mock(SearchDataService::class);

        $sl = m::mock(ServiceLocatorInterface::class)
            ->shouldReceive('getServiceLocator')->once()->andReturnSelf()
            ->shouldReceive('get')->with('DataServiceManager')->once()->andReturn(
                m::mock()
                    ->shouldReceive('get')->with(SearchDataService::class)->once()->andReturn($service)
                    ->getMock()
            )
            ->getMock();

        $sut = new SearchOrderFieldsetFactory($options);
        $result = $sut->createService($sl);

        $this->assertInstanceOf(SearchOrderFieldset::class, $result);
        $this->assertSame($service, $result->getSearchService());
    }
}
