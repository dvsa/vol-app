<?php

namespace OlcsTest\Service\Data\Search;

use Common\Service\Data\Search\SearchTypeManager;
use Common\Service\Data\Search\SearchTypeManagerFactory;
use CommonTest\Common\Service\Data\Search\Asset\SearchType;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class SearchTypeManagerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $search = new SearchType();

        $serviceConfig = ['search' => ['services' => ['testService' => $search]]];

        $sut = new SearchTypeManagerFactory();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($serviceConfig);

        $service = $sut->__invoke($mockSl, SearchTypeManager::class);

        $this->assertInstanceOf(SearchTypeManager::class, $service);
        $this->assertEquals($search, $service->get('testService'));
    }
}
