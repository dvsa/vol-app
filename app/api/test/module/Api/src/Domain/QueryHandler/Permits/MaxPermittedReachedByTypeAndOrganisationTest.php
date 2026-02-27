<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\MaxPermittedReachedByTypeAndOrganisation
    as MaxPermittedReachedByTypeAndOrganisationHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockLicenceMaxPermittedCounter;
use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByTypeAndOrganisation
    as MaxPermittedReachedByTypeAndOrganisationQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class MaxPermittedReachedByTypeAndOrganisationTest extends QueryHandlerTestCase
{
    public const ORGANISATION_ID = 5;

    public function setUp(): void
    {
        $this->sut = new MaxPermittedReachedByTypeAndOrganisationHandler();

        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityStockLicenceMaxPermittedCounter' => m::mock(StockLicenceMaxPermittedCounter::class),
            'CommonCurrentDateTimeFactory' => m::mock(CurrentDateTimeFactory::class),
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryEcmtAnnual')]
    public function testHandleQueryEcmtAnnual(
        mixed $stock1Licence1Count,
        mixed $stock1Licence2Count,
        mixed $stock2Licence1Count,
        mixed $stock2Licence2Count,
        mixed $expectedMaxPermittedReached
    ): void {
        $irhpPermitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT;

        $currentDateTime = m::mock(DateTime::class);

        $this->mockedSmServices['CommonCurrentDateTimeFactory']->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($currentDateTime);

        $irhpPermitStock1 = m::mock(IrhpPermitStock::class);

        $irhpPermitWindow1 = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow1->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock1);

        $irhpPermitStock2 = m::mock(IrhpPermitStock::class);

        $irhpPermitWindow2 = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow2->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock2);

        $openWindows = [$irhpPermitWindow1, $irhpPermitWindow2];

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByType')
            ->with($irhpPermitTypeId, $currentDateTime)
            ->andReturn($openWindows);

        $licence1 = m::mock(Licence::class);
        $licence2 = m::mock(Licence::class);

        $eligibleLicences = [$licence1, $licence2];

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getEligibleIrhpLicences')
            ->withNoArgs()
            ->andReturn($eligibleLicences);

        $this->repoMap['Organisation']->shouldReceive('fetchById')
            ->with(self::ORGANISATION_ID)
            ->andReturn($organisation);

        $this->mockedSmServices['PermitsAvailabilityStockLicenceMaxPermittedCounter']->shouldReceive('getCount')
            ->with($irhpPermitStock1, $licence1)
            ->andReturn($stock1Licence1Count);
        $this->mockedSmServices['PermitsAvailabilityStockLicenceMaxPermittedCounter']->shouldReceive('getCount')
            ->with($irhpPermitStock1, $licence2)
            ->andReturn($stock1Licence2Count);
        $this->mockedSmServices['PermitsAvailabilityStockLicenceMaxPermittedCounter']->shouldReceive('getCount')
            ->with($irhpPermitStock2, $licence1)
            ->andReturn($stock2Licence1Count);
        $this->mockedSmServices['PermitsAvailabilityStockLicenceMaxPermittedCounter']->shouldReceive('getCount')
            ->with($irhpPermitStock2, $licence2)
            ->andReturn($stock2Licence2Count);

        $result = $this->sut->handleQuery(
            MaxPermittedReachedByTypeAndOrganisationQry::create(
                [
                    'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                    'organisation' => self::ORGANISATION_ID
                ]
            )
        );

        $expectedResult = [
            'maxPermittedReached' => $expectedMaxPermittedReached
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public static function dpHandleQueryEcmtAnnual(): array
    {
        return [
            'nothing available across all windows and licences' => [0, 0, 0, 0, true],
            'allowance remaining on some windows and licences' => [5, 0, 3, 0, false],
            'allowance remaining on all windows and licences' => [12, 8, 10, 2, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryNotEcmtAnnual')]
    public function testHandleQueryNotEcmtAnnual(mixed $irhpPermitTypeId): void
    {
        $expectedResult = [
            'maxPermittedReached' => false
        ];

        $result = $this->sut->handleQuery(
            MaxPermittedReachedByTypeAndOrganisationQry::create(
                [
                    'irhpPermitType' => $irhpPermitTypeId,
                    'organisation' => self::ORGANISATION_ID
                ]
            )
        );

        $this->assertEquals($expectedResult, $result);
    }

    public static function dpHandleQueryNotEcmtAnnual(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }
}
