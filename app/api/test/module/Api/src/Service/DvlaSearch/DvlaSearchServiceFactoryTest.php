<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\DvlaSearch;

use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchService as DvlaSearchServiceClient;
use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchServiceFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class DvlaSearchServiceFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $config = [
            'dvla_search' => [
                'base_uri' => 'http://localhost',
                'proxy' => 'http://localhost',
                'api_key' => 'abc123'
            ]
        ];

        $logger = new \Dvsa\OlcsTest\SafeLogger();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('Logger')->andReturn($logger);

        $sut = new DvlaSearchServiceFactory();
        $service = $sut->__invoke($mockSl, DvlaSearchServiceClient::class);

        $this->assertInstanceOf(DvlaSearchServiceClient::class, $service);
    }
}
