<?php

namespace OlcsTest\Form\Element;

use Common\Service\Data\Search\Search as SearchDataService;
use Interop\Container\ContainerInterface;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Element\SearchOrderFieldsetFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class SearchOrderFieldsetFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $options = [
            'name' => 'element_name'
        ];

        $service = m::mock(SearchDataService::class);

        $sl = m::mock(ContainerInterface::class)
            ->shouldReceive('get')->with('DataServiceManager')->once()->andReturn(
                m::mock()
                    ->shouldReceive('get')->with(SearchDataService::class)->once()->andReturn($service)
                    ->getMock()
            )
            ->getMock();

        $sut = new SearchOrderFieldsetFactory($options);
        $result = $sut->__invoke($sl, SearchOrderFieldset::class);

        $this->assertInstanceOf(SearchOrderFieldset::class, $result);
        $this->assertSame($service, $result->getSearchService());
    }
}
