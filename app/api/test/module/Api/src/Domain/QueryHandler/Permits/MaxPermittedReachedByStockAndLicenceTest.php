<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\MaxPermittedReachedByStockAndLicence
    as MaxPermittedReachedByStockAndLicenceHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockLicenceMaxPermittedCounter;
use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByStockAndLicence
    as MaxPermittedReachedByStockAndLicenceQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class MaxPermittedReachedByStockAndLicenceTest extends QueryHandlerTestCase
{
    public const IRHP_PERMIT_STOCK_ID = 40;

    public const LICENCE_ID = 7;

    private $irhpPermitType;

    private $irhpPermitStock;

    public function setUp(): void
    {
        $this->sut = new MaxPermittedReachedByStockAndLicenceHandler();

        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityStockLicenceMaxPermittedCounter' => m::mock(StockLicenceMaxPermittedCounter::class)
        ];

        $this->irhpPermitType = m::mock(IrhpPermitType::class);

        $this->irhpPermitStock = m::mock(IrhpPermitStock::class);
        $this->irhpPermitStock->shouldReceive('getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($this->irhpPermitType);

        parent::setUp();
    }

    public function testHandleQueryNotEcmtAnnual(): void
    {
        $this->irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturnFalse();

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with(self::IRHP_PERMIT_STOCK_ID)
            ->andReturn($this->irhpPermitStock);

        $expectedResult = [
            'maxPermittedReached' => false
        ];

        $result = $this->sut->handleQuery(
            MaxPermittedReachedByStockAndLicenceQry::create(
                [
                    'irhpPermitStock' => self::IRHP_PERMIT_STOCK_ID,
                    'licence' => self::LICENCE_ID
                ]
            )
        );

        $this->assertEquals($expectedResult, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryEcmtAnnual')]
    public function testHandleQueryEcmtAnnual(mixed $maxPermittedCount, mixed $expectedMaxPermittedReached): void
    {
        $this->irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturnTrue();

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with(self::IRHP_PERMIT_STOCK_ID)
            ->andReturn($this->irhpPermitStock);

        $licence = m::mock(Licence::class);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LICENCE_ID)
            ->andReturn($licence);

        $this->mockedSmServices['PermitsAvailabilityStockLicenceMaxPermittedCounter']->shouldReceive('getCount')
            ->with($this->irhpPermitStock, $licence)
            ->andReturn($maxPermittedCount);

        $expectedResult = [
            'maxPermittedReached' => $expectedMaxPermittedReached
        ];

        $result = $this->sut->handleQuery(
            MaxPermittedReachedByStockAndLicenceQry::create(
                [
                    'irhpPermitStock' => self::IRHP_PERMIT_STOCK_ID,
                    'licence' => self::LICENCE_ID
                ]
            )
        );

        $this->assertEquals($expectedResult, $result);
    }

    public static function dpHandleQueryEcmtAnnual(): array
    {
        return [
            [2, false],
            [1, false],
            [0, true],
        ];
    }
}
