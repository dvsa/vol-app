<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Cqrs\Query;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QuerySenderTest extends MockeryTestCase
{
    protected $sut;

    protected $mockQueryService;

    protected $mockAnnotationBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new QuerySender();

        $this->mockQueryService = m::mock(CachingQueryService::class);
        $this->mockAnnotationBuilder = m::mock();

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('QueryService')->andReturn($this->mockQueryService);
        $sm->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($this->mockAnnotationBuilder);

        $this->sut->__invoke($sm, QuerySender::class);
    }

    public function testSend(): void
    {
        $query = m::mock(QueryInterface::class);
        $constructedQuery = m::mock(QueryContainerInterface::class);

        $this->mockAnnotationBuilder->shouldReceive('createQuery')
            ->once()
            ->with($query)
            ->andReturn($constructedQuery);

        $this->mockQueryService
            ->shouldReceive('setRecoverHttpClientException')
            ->once()
            ->shouldReceive('send')
            ->once()
            ->with($constructedQuery)
            ->andReturn('RESULT');

        $this->assertEquals('RESULT', $this->sut->send($query));
    }
}
