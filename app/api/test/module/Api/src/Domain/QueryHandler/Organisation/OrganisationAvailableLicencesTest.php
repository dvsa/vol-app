<?php

declare(strict_types=1);

/**
 * Organisation Permits Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\OrganisationAvailableLicences;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationAvailableLicences as Qry;
use Mockery as m;

class OrganisationAvailableLicencesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OrganisationAvailableLicences();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('IrhpPermitType', IrhpPermitTypeRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityStockAvailabilityChecker' => m::mock(StockAvailabilityChecker::class)
        ];

        parent::setUp();
    }

    public function testHandleQueryTypeMismatch(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(OrganisationAvailableLicences::ERR_TYPE_MISMATCH);

        $permitTypeId = 22;
        $stockId = 33;
        $query = m::mock(Qry::class);
        $query->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($permitTypeId);
        $query->shouldReceive('getIrhpPermitStock')->once()->withNoArgs()->andReturn($stockId);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType->getId')->once()->andReturn(999);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->once()
            ->with($stockId)
            ->andReturn($irhpPermitStock);

        $this->sut->handleQuery($query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryProvider')]
    public function testHandleQuery(mixed $isShortTerm, mixed $permitsAvailable, mixed $eligibleLicences, mixed $hasEligibleLicences): void
    {
        $permitTypeId = 22;
        $stockId = 33;
        $hasOpenWindow = true;
        $isEcmtAnnual = false;
        $isBilateral = false;
        $query = m::mock(Qry::class);
        $query->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($permitTypeId);
        $query->shouldReceive('getIrhpPermitStock')->once()->withNoArgs()->andReturn($stockId);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType->getId')->once()->withNoArgs()->andReturn($permitTypeId);
        $irhpPermitStock->shouldReceive('hasOpenWindow')->once()->withNoArgs()->andReturn($hasOpenWindow);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->once()
            ->with($stockId)
            ->andReturn($irhpPermitStock);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')->once()->withNoArgs()->andReturn($isShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')->once()->withNoArgs()->andReturn($isEcmtAnnual);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturn($isBilateral);
        $irhpPermitType->shouldReceive('usesMultiStockLicenceBehaviour')->never();

        $this->repoMap['IrhpPermitType']
            ->shouldReceive('fetchById')
            ->once()
            ->with($permitTypeId)
            ->andReturn($irhpPermitType);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getEligibleIrhpLicencesForStock')
            ->with($irhpPermitStock)
            ->once()
            ->andReturn($eligibleLicences);

        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($organisation);

        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']
            ->shouldReceive('hasAvailability')
            ->times($isShortTerm ? 1 : 0)
            ->with($stockId)
            ->andReturn($permitsAvailable);

        $expected = [
            'hasOpenWindow' => $hasOpenWindow,
            'isEcmtAnnual' => $isEcmtAnnual,
            'isBilateral' => $isBilateral,
            'permitTypeId' => $permitTypeId,
            'eligibleLicences' => $eligibleLicences,
            'hasEligibleLicences' => $hasEligibleLicences,
            'permitsAvailable' => $permitsAvailable,
            'selectedLicence' => null,
        ];

        static::assertEquals($expected, $this->sut->handleQuery($query));
    }

    public static function dpHandleQueryProvider(): array
    {
        return [
            [true, false, ['eligiblelicences'], true],
            [true, true, ['eligiblelicences'], true],
            [false, true, ['eligiblelicences'], true],
            [true, false, [], false],
            [true, true, [], false],
            [false, true, [], false],
        ];
    }

    public function testHandleQueryMultiStockNoStockId(): void
    {
        $permitTypeId = 22;
        $isBilateral = false;
        $eligibleLicences = ['eligiblelicences'];
        $query = m::mock(Qry::class);
        $query->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($permitTypeId);
        $query->shouldReceive('getIrhpPermitStock')->once()->withNoArgs()->andReturnNull();

        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $window = m::mock(IrhpPermitWindow::class);
        $window->shouldReceive('getIrhpPermitStock')->once()->withNoArgs()->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchLastOpenWindowByIrhpPermitType')
            ->once()
            ->with($permitTypeId, m::type(DateTime::class))
            ->andReturn($window);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getEligibleIrhpLicencesForStock')
            ->with($irhpPermitStock)
            ->once()
            ->andReturn($eligibleLicences);

        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($organisation);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('usesMultiStockLicenceBehaviour')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $irhpPermitType->shouldReceive('isBilateral')->withNoArgs()->andReturn($isBilateral);

        $this->repoMap['IrhpPermitType']
            ->shouldReceive('fetchById')
            ->with($permitTypeId)
            ->andReturn($irhpPermitType);

        $expected = [
            'hasOpenWindow' => true,
            'isEcmtAnnual' => false,
            'isBilateral' => false,
            'permitTypeId' => $permitTypeId,
            'eligibleLicences' => $eligibleLicences,
            'hasEligibleLicences' => true,
            'permitsAvailable' => true,
            'selectedLicence' => null,
        ];

        static::assertEquals($expected, $this->sut->handleQuery($query));
    }

    public function testHandleQueryMultiStockNoStockIdOrWindow(): void
    {
        $permitTypeId = 22;
        $isBilateral = false;

        $query = m::mock(Qry::class);
        $query->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($permitTypeId);
        $query->shouldReceive('getIrhpPermitStock')->once()->withNoArgs()->andReturnNull();

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchLastOpenWindowByIrhpPermitType')
            ->once()
            ->with($permitTypeId, m::type(DateTime::class))
            ->andThrow(NotFoundException::class);

        $organisation = m::mock(Organisation::class);

        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($organisation);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('usesMultiStockLicenceBehaviour')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $irhpPermitType->shouldReceive('isBilateral')->withNoArgs()->andReturn($isBilateral);

        $this->repoMap['IrhpPermitType']
            ->shouldReceive('fetchById')
            ->with($permitTypeId)
            ->andReturn($irhpPermitType);

        $expected = [
            'hasOpenWindow' => false,
            'isEcmtAnnual' => false,
            'isBilateral' => $isBilateral,
            'permitTypeId' => $permitTypeId,
            'eligibleLicences' => [],
            'hasEligibleLicences' => false,
            'permitsAvailable' => true,
            'selectedLicence' => null,
        ];

        static::assertEquals($expected, $this->sut->handleQuery($query));
    }
}
