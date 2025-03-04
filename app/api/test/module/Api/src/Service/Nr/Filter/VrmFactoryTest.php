<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter;

use Dvsa\Olcs\Api\Service\Nr\Filter\Vrm;
use Dvsa\Olcs\Api\Service\Nr\Filter\VrmFactory;
use Dvsa\Olcs\Transfer\Filter\Vrm as TransferVrmFilter;
use Laminas\Filter\FilterPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class VrmFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $mockTransferFilter = m::mock(TransferVrmFilter::class);

        $mockFilterPluginManager = m::mock(FilterPluginManager::class);
        $mockFilterPluginManager->shouldReceive('get')->with(TransferVrmFilter::class)->andReturn($mockTransferFilter);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(FilterPluginManager::class)->andReturn($mockFilterPluginManager);

        $sut = new VrmFactory();
        $service = $sut->__invoke($mockSl, Vrm::class);

        $this->assertInstanceOf(Vrm::class, $service);
        $this->assertSame($mockTransferFilter, $service->getVrmFilter());
    }
}
