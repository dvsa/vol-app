<?php

declare(strict_types=1);

/**
 * Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Interim;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Transfer\Query\Application\Interim as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class InterimTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Interim();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handlerQueryProvider')]
    public function testHandleQuery(mixed $status, mixed $isInterimRequested, mixed $isInterimInforce, mixed $canSetStatus, mixed $canUpdatedInterim): void
    {
        $query = Qry::create(['id' => 111]);

        $statusObj = m::mock(RefData::class)->makePartial();
        $statusObj->setId($status);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getFees')
            ->andReturn([]);
        $application->setInterimStatus($statusObj);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $result = $this->sut->handleQuery($query);

        $data = $result->serialize();

        $this->assertEquals($isInterimRequested, $data['isInterimRequested']);
        $this->assertEquals($isInterimInforce, $data['isInterimInforce']);
        $this->assertEquals($canSetStatus, $data['canSetStatus']);
        $this->assertEquals($canUpdatedInterim, $data['canUpdateInterim']);
    }

    public function testHandleQueryBundleKeepsOperatingCentresAddedOrUpdatedAndVehiclesNotRemovedWhenTheListsAreLoaded(): void
    {
        $query = Qry::create(['id' => 111]);

        $added = $this->operatingCentre('A');
        $updated = $this->operatingCentre('U');
        $deleted = $this->operatingCentre('D');

        $notRemoved = new LicenceVehicle(m::mock(Licence::class)->shouldIgnoreMissing(), new Vehicle());
        $removed = new LicenceVehicle(m::mock(Licence::class)->shouldIgnoreMissing(), new Vehicle());
        $removed->setRemovalDate('2026-01-02T03:04:05+0000');

        $ocs = null;
        $vehicles = null;

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('serialize')
            ->once()
            ->andReturnUsing(
                function (array $bundle) use ($added, $updated, $deleted, $notRemoved, $removed, &$ocs, &$vehicles) {
                    $ocs = (new ArrayCollection([$deleted, $added, $updated]))
                        ->matching($bundle['operatingCentres']['criteria'])
                        ->getValues();
                    $vehicles = (new ArrayCollection([$removed, $notRemoved]))
                        ->matching($bundle['licenceVehicles']['criteria'])
                        ->getValues();

                    return [];
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->sut->handleQuery($query)->serialize();

        $this->assertSame([$added, $updated], $ocs);
        $this->assertSame([$notRemoved], $vehicles);
    }

    private function operatingCentre(string $action): ApplicationOperatingCentre
    {
        $oc = new ApplicationOperatingCentre(m::mock(ApplicationEntity::class), new OperatingCentre());
        $oc->setAction($action);

        return $oc;
    }

    public static function handlerQueryProvider(): \Iterator
    {
        yield [
            ApplicationEntity::INTERIM_STATUS_REQUESTED,
            true,
            false,
            false,
            true
        ];
        yield [
            ApplicationEntity::INTERIM_STATUS_INFORCE,
            false,
            true,
            true,
            true
        ];
        yield [
            ApplicationEntity::INTERIM_STATUS_REFUSED,
            false,
            false,
            true,
            false
        ];
        yield [
            ApplicationEntity::INTERIM_STATUS_REVOKED,
            false,
            false,
            true,
            false
        ];
        yield [
            ApplicationEntity::INTERIM_STATUS_GRANTED,
            false,
            false,
            true,
            true
        ];
    }
}
