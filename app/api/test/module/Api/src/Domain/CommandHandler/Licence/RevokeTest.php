<?php

declare(strict_types=1);

/**
 * RevokeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\Revoke as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\RemoveLicenceStatusRulesForLicence;
use Dvsa\Olcs\Api\Domain\Command\LicenceVehicle\RemoveLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Domain\Command\Licence\ReturnAllCommunityLicences;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Tm\DeleteTransportManagerLicence;
use Dvsa\Olcs\Api\Domain\Command\Variation\EndInterim;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\RevokeLicence as Command;
use Mockery as m;

/**
 * RevokeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class RevokeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::LICENCE_STATUS_REVOKED,
            LicenceEntity::LICENCE_CATEGORY_PSV,
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
        ];

        $this->references = [

        ];

        parent::initReferences();
    }

    public function testHandleCommandPsv(): void
    {
        $command = Command::create(['id' => 532]);

        $licence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(532);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_PSV]);
        $licence->setCommunityLics(
            new ArrayCollection(
                [
                    new CommunityLic(),
                    new CommunityLic()
                ]
            )
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (LicenceEntity $saveLicence) {
                $this->assertSame($this->refData['lsts_revoked'], $saveLicence->getStatus());
                $this->assertInstanceOf(\DateTime::class, $saveLicence->getRevokedDate());
                $this->assertSame((new \DateTime())->format('Y-m-d'), $saveLicence->getRevokedDate()->format('Y-m-d'));
            }
        );

        $this->expectedSideEffect(
            EndInterim::class,
            ['licenceId' => 532],
            new Result()
        );

        $ceaseDiscsResult = new Result();
        $this->expectedSideEffect(
            CeasePsvDiscs::class,
            ['licence' => 532],
            $ceaseDiscsResult
        );

        $removeVehicleResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 532, 'id' => null],
            $removeVehicleResult
        );

        $removeTmResult = new Result();
        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licence, 'id' => null],
            $removeTmResult
        );

        $removeRulesResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceStatusRulesForLicence::class,
            [
                'licence' => $licence
            ],
            $removeRulesResult
        );

        $this->expectedSideEffect(
            ReturnAllCommunityLicences::class,
            [
                'id' => 532
            ],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Publication\Licence::class,
            ['id' => 532],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => 532,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED,
                'context' => EndIrhpApplicationsAndPermits::CONTEXT_REVOKE,
            ],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(532);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Licence ID 532 revoked"], $result->getMessages());
    }

    public function testHandleCommandPsvSpecialRestricted(): void
    {
        $command = Command::create(['id' => 532]);

        $licence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(532);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_PSV]);
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED]);
        $licence->setCommunityLics(
            new ArrayCollection(
                [
                    new CommunityLic(),
                    new CommunityLic()
                ]
            )
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (LicenceEntity $saveLicence) {
                $this->assertSame($this->refData['lsts_revoked'], $saveLicence->getStatus());
                $this->assertInstanceOf(\DateTime::class, $saveLicence->getRevokedDate());
                $this->assertSame((new \DateTime())->format('Y-m-d'), $saveLicence->getRevokedDate()->format('Y-m-d'));
            }
        );

        $ceaseDiscsResult = new Result();
        $this->expectedSideEffect(
            CeasePsvDiscs::class,
            ['licence' => 532],
            $ceaseDiscsResult
        );

        $removeVehicleResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 532, 'id' => null],
            $removeVehicleResult
        );

        $removeTmResult = new Result();
        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licence, 'id' => null],
            $removeTmResult
        );

        $removeRulesResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceStatusRulesForLicence::class,
            [
                'licence' => $licence
            ],
            $removeRulesResult
        );

        $this->expectedSideEffect(
            ReturnAllCommunityLicences::class,
            [
                'id' => 532
            ],
            new Result()
        );

        $this->expectedSideEffect(
            EndInterim::class,
            ['licenceId' => 532],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => 532,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED
            ],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(532);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Licence ID 532 revoked"], $result->getMessages());
    }

    public function testHandleCommandGoods(): void
    {
        $command = Command::create(['id' => 532]);

        $licence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(532);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (LicenceEntity $saveLicence) {
                $this->assertSame($this->refData['lsts_revoked'], $saveLicence->getStatus());
                $this->assertInstanceOf(\DateTime::class, $saveLicence->getRevokedDate());
                $this->assertSame((new \DateTime())->format('Y-m-d'), $saveLicence->getRevokedDate()->format('Y-m-d'));
            }
        );

        $ceaseDiscsResult = new Result();
        $this->expectedSideEffect(
            CeaseGoodsDiscs::class,
            ['licence' => 532],
            $ceaseDiscsResult
        );

        $removeVehicleResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 532, 'id' => null],
            $removeVehicleResult
        );

        $removeTmResult = new Result();
        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licence, 'id' => null],
            $removeTmResult
        );

        $removeRulesResult = new Result();
        $this->expectedSideEffect(
            RemoveLicenceStatusRulesForLicence::class,
            [
                'licence' => $licence
            ],
            $removeRulesResult
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Publication\Licence::class,
            ['id' => 532],
            new Result()
        );

        $this->expectedSideEffect(
            EndInterim::class,
            ['licenceId' => 532],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => 532,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED
            ],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(532);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Licence ID 532 revoked"], $result->getMessages());
    }

    public function testHandleCommandApplications(): void
    {
        $command = Command::create(['id' => 532]);

        $licence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(532);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);

        $this->stubApplication($licence, 174, Application::APPLICATION_STATUS_CURTAILED, true);
        $this->stubApplication($licence, 224, Application::APPLICATION_STATUS_CURTAILED, false);
        $this->stubApplication($licence, 345, Application::APPLICATION_STATUS_NOT_SUBMITTED, true);
        $this->stubApplication($licence, 445, Application::APPLICATION_STATUS_NOT_SUBMITTED, false);
        $this->stubApplication($licence, 567, Application::APPLICATION_STATUS_UNDER_CONSIDERATION, true);
        $this->stubApplication($licence, 667, Application::APPLICATION_STATUS_UNDER_CONSIDERATION, false);
        $this->stubApplication($licence, 778, Application::APPLICATION_STATUS_GRANTED, true);
        $this->stubApplication($licence, 878, Application::APPLICATION_STATUS_VALID, true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->once();

        $this->expectedSideEffect(
            CeaseGoodsDiscs::class,
            ['licence' => 532],
            new Result()
        );
        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 532, 'id' => null],
            new Result()
        );
        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licence, 'id' => null],
            new Result()
        );
        $this->expectedSideEffect(
            RemoveLicenceStatusRulesForLicence::class,
            ['licence' => $licence],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\DeleteApplication::class,
            ['id' => 345],
            (new Result())->addMessage('NOT_SUBMITTED')
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\RefuseApplication::class,
            ['id' => 567],
            (new Result())->addMessage('UNDER_CONSIDERATION')
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Publication\Licence::class,
            ['id' => 532],
            new Result()
        );
        $this->expectedSideEffect(
            EndInterim::class,
            ['licenceId' => 532],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => 532,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED
            ],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(532);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'NOT_SUBMITTED',
                'UNDER_CONSIDERATION',
                'Licence ID 532 revoked'
            ],
            $result->getMessages()
        );
    }

    /**
     *
     * @param LicenceEntity $licence
     * @param int     $id
     * @param string  $status
     * @param bool    $isVariation
     *
     * @return Application
     */
    private function stubApplication(mixed $licence, mixed $id, mixed $status, bool $isVariation = false): mixed
    {
        $application = new Application($licence, (new RefData())->setId($status), $isVariation);
        $application->setId($id);
        $licence->addApplications($application);

        return $application;
    }
}
