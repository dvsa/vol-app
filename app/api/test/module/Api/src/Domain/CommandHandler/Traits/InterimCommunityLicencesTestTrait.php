<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\UpdateInterim;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\UpdateInterim as ApplicationCommand;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateInterim as VariationCommand;
use Mockery as m;

trait InterimCommunityLicencesTestTrait
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpManualInterimCommunityLicences')]
    public function testManualInterimCommunityLicences(string $previousStatus, string $newStatus, bool $activates): void
    {
        $isVariation = $this->sut instanceof UpdateInterim;
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setLicenceType(new RefData(
            $isVariation ? Licence::LICENCE_TYPE_STANDARD_NATIONAL : Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ));
        $licence->setStatus(new RefData(
            $isVariation ? Licence::LICENCE_STATUS_VALID : Licence::LICENCE_STATUS_UNDER_CONSIDERATION
        ));

        $copies = [];
        foreach ([0, 1, 2] as $issueNo) {
            $copy = new CommunityLic();
            $copy->setId(100 + $issueNo);
            $copy->setIssueNo($issueNo);
            $copy->setLicence($licence);
            $copy->setStatus(new RefData(CommunityLic::STATUS_PENDING));
            $copies[] = $copy;
        }
        $licence->setCommunityLics(new ArrayCollection($copies));

        $application = new Application($licence, new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), $isVariation);
        $application->setId(111);
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));
        $application->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));
        $application->updateTotAuthHgvVehicles(2);
        $application->setTotAuthTrailers(0);
        $application->setInterimStatus(new RefData($previousStatus));

        foreach ([$newStatus, CommunityLic::STATUS_ACTIVE] as $status) {
            $this->refData[$status] = new RefData($status);
        }

        $commandClass = $isVariation ? VariationCommand::class : ApplicationCommand::class;
        $command = $commandClass::create([
            'id' => 111,
            'version' => 1,
            'requested' => 'Y',
            'status' => $newStatus,
            'reason' => 'Interim authority',
            'authHgvVehicles' => 2,
            'authTrailers' => 0,
            'startDate' => '2026-09-01',
            'endDate' => '2026-12-01',
        ]);

        $saved = false;
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)->andReturn($application);
        $this->repoMap['Application']->shouldReceive('save')->with($application)->once()
            ->andReturnUsing(function () use ($application, $newStatus, &$saved): void {
                $this->assertSame($newStatus, $application->getCurrentInterimStatus());
                $this->assertSame('2026-09-01', $application->getInterimStart()->format('Y-m-d'));
                $this->assertSame('2026-12-01', $application->getInterimEnd()->format('Y-m-d'));
                $saved = true;
            });

        $savedCopies = [];
        $this->repoMap['CommunityLic']->shouldReceive('save')
            ->andReturnUsing(function (CommunityLic $copy) use (&$saved, &$savedCopies): void {
                $this->assertTrue($saved, 'Save the interim before activating its community licences');
                $savedCopies[] = $copy->getId();
            });
        $batches = [];
        $this->commandHandler->shouldReceive('handleCommand')->with(m::type(GenerateBatch::class), false)
            ->andReturnUsing(function (GenerateBatch $batch) use (&$batches): Result {
                $batches[] = $batch->getArrayCopy();
                return new Result();
            });

        $this->sut->handleCommand($command);

        foreach ($copies as $copy) {
            $this->assertSame(
                $activates ? CommunityLic::STATUS_ACTIVE : CommunityLic::STATUS_PENDING,
                $copy->getStatus()->getId()
            );
            if ($activates) {
                $this->assertSame(date('Y-m-d'), $copy->getSpecifiedDate()->format('Y-m-d'));
            } else {
                $this->assertNull($copy->getSpecifiedDate());
            }
        }
        $this->assertSame($activates ? [100, 101, 102] : [], $savedCopies);
        $this->assertCount($activates ? 1 : 0, $batches);
        if ($activates) {
            $this->assertSame(111, $batches[0]['identifier']);
            $this->assertSame(222, $batches[0]['licence']);
            $this->assertSame([100, 101, 102], $batches[0]['communityLicenceIds']);
            $this->assertFalse($batches[0]['isBatchReprint']);
        }
    }

    public static function dpManualInterimCommunityLicences(): array
    {
        return [
            'grant to in-force activates office and certified copies' => [
                Application::INTERIM_STATUS_GRANTED, Application::INTERIM_STATUS_INFORCE, true,
            ],
            'in-force edit does not generate copies' => [
                Application::INTERIM_STATUS_INFORCE, Application::INTERIM_STATUS_INFORCE, false,
            ],
            'granted alone leaves copies pending' => [
                Application::INTERIM_STATUS_REQUESTED, Application::INTERIM_STATUS_GRANTED, false,
            ],
        ];
    }
}
