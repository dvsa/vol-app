<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\VariationFinancialEvidenceAdapter;

class VariationFinancialEvidenceAdapterTest extends MockeryTestCase
{
    public function testAlterFormForLva(): void
    {
        $container = m::mock(ContainerInterface::class);
        $sut = new VariationFinancialEvidenceAdapter($container);

        $mockElement = m::mock()
            ->shouldReceive('setValue')
            ->once()
            ->with('markup-required-finance-application')
            ->getMock();

        $mockFieldset = m::mock()
            ->shouldReceive('get')
            ->once()
            ->with('requiredFinance')
            ->andReturn($mockElement)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->once()
            ->with('finance')
            ->andReturn($mockFieldset)
            ->getMock();

        $sut->alterFormForLva($mockForm);
    }
}
