<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\EmissionsByYear;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Permits\EmissionsByYear as EmissionsByYearQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use DateTime;

class EmissionsByYearTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EmissionsByYear();
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $ipw1 = m::mock(IrhpPermitWindow::class)->makePartial();
        $ips = m::mock(IrhpPermitStock::class);
        $ipw1->setIrhpPermitStock($ips);

        $query = EmissionsByYearQuery::create(
            [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                'year' => 3000
            ]
        );

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByTypeYear')
            ->with(
                $query->getIrhpPermitType(),
                m::type(DateTime::class),
                3000
            )
            ->andReturn([$ipw1]);

        $ipw1->shouldReceive('getIrhpPermitStock->getValidTo->format')
            ->with('Y')
            ->once()
            ->andReturn(3000);

        $ipw1->shouldReceive('getIrhpPermitStock->hasEuro5Range')
            ->once()
            ->andReturn(true);

        $ipw1->shouldReceive('getIrhpPermitStock->hasEuro6Range')
            ->once()
            ->andReturn(true);

        $this->assertEquals(
            [
                'yearEmissions' => [3000 => ['euro5' => true, 'euro6' => true]],
            ],
            $this->sut->handleQuery($query)
        );
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQueryUnsupportedType')]
    public function testHandleQueryUnsupportedType(mixed $unsupportedTypeId): void
    {
        $query = EmissionsByYearQuery::create(
            [
                'irhpPermitType' => $unsupportedTypeId,
                'year' => 3000
            ]
        );

        $this->assertEquals(
            [
                'yearEmissions' => []
            ],
            $this->sut->handleQuery($query)
        );
    }

    public static function dpTestHandleQueryUnsupportedType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
        ];
    }

    public function testHandleQueryNoYears(): void
    {
        $query = EmissionsByYearQuery::create(
            [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                'year' => 3000
            ]
        );

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByTypeYear')
            ->with(
                $query->getIrhpPermitType(),
                m::type(DateTime::class),
                3000
            )
            ->andReturn([]);

        $this->assertEquals(
            [
                'yearEmissions' => [],
            ],
            $this->sut->handleQuery($query)
        );
    }
}
