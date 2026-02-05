<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Laminas\ServiceManager\ServiceManager;

/**
 * External Transport Managers Adapter Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapterTest extends MockeryTestCase
{
    public const LICENCE_ID = 612;

    /** @var  LicenceTransportManagerAdapter|\Mockery\MockInterface */
    protected $sut;
    /** @var  VariationLvaService|\Mockery\MockInterface */
    protected $mockLvaVariationSrv;

    protected $mockContainer;

    public function setUp(): void
    {
        /** @var TransferAnnotationBuilder $mockAnnotationBuilder */
        $mockAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        /** @var CachingQueryService $mockQuerySrv */
        $mockQuerySrv = m::mock(CachingQueryService::class);
        /** @var CommandService $mockCommandSrv */
        $mockCommandSrv = m::mock(CommandService::class);

        $this->mockLvaVariationSrv = m::mock(VariationLvaService::class);

        $this->mockContainer = m::mock(ContainerInterface::class);

        $this->sut = new LicenceTransportManagerAdapter(
            $mockAnnotationBuilder,
            $mockQuerySrv,
            $mockCommandSrv,
            $this->mockLvaVariationSrv,
            $this->mockContainer
        );
    }

    public function testAddMessages(): void
    {
        $this->mockLvaVariationSrv
            ->shouldReceive('addVariationMessage')
            ->with(self::LICENCE_ID, 'transport_managers', 'variation-message-add-tm')
            ->once();

        $this->sut->addMessages(self::LICENCE_ID);
    }
}
