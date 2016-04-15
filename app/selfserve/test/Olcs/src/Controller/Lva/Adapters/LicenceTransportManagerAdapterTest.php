<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Zend\ServiceManager\ServiceManager;

/**
 * External Transport Managers Adapter Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapterTest extends MockeryTestCase
{
    const LICENCE_ID = 612;

    /** @var  LicenceTransportManagerAdapter|\Mockery\MockInterface */
    protected $sut;
    /** @var  VariationLvaService|\Mockery\MockInterface */
    protected $mockLvaVariationSrv;

    public function setUp()
    {
        /** @var TransferAnnotationBuilder $mockAnnotationBuilder */
        $mockAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        /** @var CachingQueryService $mockQuerySrv */
        $mockQuerySrv = m::mock(CachingQueryService::class);
        /** @var CommandService $mockCommandSrv */
        $mockCommandSrv = m::mock(CommandService::class);

        $this->mockLvaVariationSrv = m::mock(VariationLvaService::class);

        $this->sut = new LicenceTransportManagerAdapter(
            $mockAnnotationBuilder, $mockQuerySrv, $mockCommandSrv, $this->mockLvaVariationSrv
        );
    }

    public function testAddMessages()
    {
        $this->mockLvaVariationSrv
            ->shouldReceive('addVariationMessage')
            ->with(self::LICENCE_ID, 'transport_managers')
            ->once();

        $this->sut->addMessages(self::LICENCE_ID);
    }
}
