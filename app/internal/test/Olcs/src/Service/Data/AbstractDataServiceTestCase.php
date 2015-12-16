<?php

/**
 * Abstract Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AbstractDataServiceTestCase extends MockeryTestCase
{
    protected $mockServiceLocator;

    public function mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse)
    {
        $this->mockServiceLocator = m::mock('\Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('TransferAnnotationBuilder')
            ->andReturn($mockTransferAnnotationBuilder)
            ->once()
            ->shouldReceive('get')
            ->with('QueryService')
            ->andReturn(
                m::mock()
                    ->shouldReceive('send')
                    ->with('query')
                    ->andReturn($mockResponse)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $sut->setServiceLocator($this->mockServiceLocator);
    }
}
