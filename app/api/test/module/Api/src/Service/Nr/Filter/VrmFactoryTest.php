<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter;

use Dvsa\Olcs\Api\Service\Nr\Filter\Vrm;
use Dvsa\Olcs\Api\Service\Nr\Filter\VrmFactory;
use Dvsa\Olcs\Transfer\Filter\Vrm as TransferVrmFilter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

/**
 * Class VrmFactoryTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\Filter
 */
class VrmFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $mockTransferFilter = m::mock(TransferVrmFilter::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(TransferVrmFilter::class)->andReturn($mockTransferFilter);

        $sut = new VrmFactory();
        $service = $sut->__invoke($mockSl, Vrm::class);

        $this->assertInstanceOf(Vrm::class, $service);
        $this->assertSame($mockTransferFilter, $service->getVrmFilter());
    }
}
