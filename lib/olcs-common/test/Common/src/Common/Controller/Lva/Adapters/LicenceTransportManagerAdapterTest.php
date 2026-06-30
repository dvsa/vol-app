<?php

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Dvsa\Olcs\Transfer\Command\CommandContainer;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Http\Response as HttpResponse;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete;

class LicenceTransportManagerAdapterTest extends MockeryTestCase
{
    /** @var LicenceTransportManagerAdapter */
    protected $sut;

    /** @var  ContainerInterface|\Mockery\MockInterface */
    protected $container;

    /** @var TransferAnnotationBuilder $mockAnnotationBuilder */
    protected $mockAnnotationBuilder;

    /** @var CachingQueryService $mockQuerySrv */
    protected $mockQuerySrv;

    /** @var CommandService $mockCommandSrv */
    protected $mockCommandSrv;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        $this->mockAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        $this->mockQuerySrv = m::mock(CachingQueryService::class);
        $this->mockCommandSrv = m::mock(CommandService::class);

        $this->sut = new LicenceTransportManagerAdapter(
            $this->mockAnnotationBuilder,
            $this->mockQuerySrv,
            $this->mockCommandSrv,
            $this->container
        );
    }

    public function testDelete(): void
    {
        $responseIsOk = true;
        $httpResponse = m::mock(HttpResponse::class);
        $httpResponse->shouldReceive('isOk')->once()->withNoArgs()->andReturn($responseIsOk);
        $commandContainer = m::mock(CommandContainer::class);

        $this->mockAnnotationBuilder
            ->shouldReceive('createCommand')
            ->with(m::type(Delete::class))
            ->once()
            ->andReturn($commandContainer);

        $this->mockCommandSrv->shouldReceive('send')->with($commandContainer)->once()->andReturn($httpResponse);

        $this->assertEquals($responseIsOk, $this->sut->delete([111, 222], 333));
    }
}
