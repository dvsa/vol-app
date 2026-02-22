<?php

declare(strict_types=1);

/**
 * Create Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Sla;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateApplication;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber;

/**
 * Create Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateApplication();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('Sla', Sla::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            SlaCalculatorInterface::class => m::mock(SlaCalculator::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_NOT_SUBMITTED,
            Licence::LICENCE_STATUS_UNDER_CONSIDERATION,
            ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED,
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            ApplicationEntity::APPLIED_VIA_PHONE,
            ApplicationEntity::APPLIED_VIA_SELFSERVE,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_HGV,
        ];

        $this->references = [
            Organisation::class => [
                11 => m::mock(Organisation::class)
            ],
            TrafficArea::class => [
                TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE => m::mock(TrafficArea::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandMinimal(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->times(3)
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false)
            ->shouldReceive('isGranted')
            ->twice()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $this->repoMap['Sla']->expects('fetchByCategoryFieldAndCompareTo')->never();

        $command = Cmd::create(['organisation' => 11]);
        /** @var ApplicationEntity $app */
        $app = null;

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $application) use (&$app) {
                    $app = $application;
                    $application->setId(22);
                    $application->getLicence()->setId(33);
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 22,
                'licence' => 33
            ],
            'messages' => [
                'Licence created',
                'Application created',
                'Application Completion created',
                'Application Tracking created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ApplicationEntity::class, $app);
        $this->assertInstanceOf(ApplicationCompletion::class, $app->getApplicationCompletion());
        $this->assertInstanceOf(ApplicationTracking::class, $app->getApplicationTracking());
        $this->assertInstanceOf(Licence::class, $app->getLicence());
        $this->assertSame($this->references[Organisation::class][11], $app->getLicence()->getOrganisation());
        $this->assertSame($this->refData[Licence::LICENCE_STATUS_NOT_SUBMITTED], $app->getLicence()->getStatus());
        $this->assertSame($this->refData[ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED], $app->getStatus());
        $this->assertSame($this->refData[ApplicationEntity::APPLIED_VIA_SELFSERVE], $app->getAppliedVia());

        $this->assertNull($app->getReceivedDate());
        $this->assertNull($app->getNiFlag());
        $this->assertNull($app->getLicenceType());
        $this->assertNull($app->getGoodsOrPsv());
        $this->assertNull($app->getLicence()->getTrafficArea());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('environmentProvider')]
    public function testHandleCommand(mixed $isInternal, mixed $isExternal, mixed $licenceStatus, mixed $appliedVia): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->times(4)
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn($isInternal)
            ->shouldReceive('isGranted')
            ->twice()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn($isExternal);

        $command = Cmd::create(
            [
                'organisation' => 11,
                'receivedDate' => '2015-01-01',
                'trafficArea' => TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE,
                'niFlag' => 'Y',
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE
            ]
        );

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn(\DateTime::createFromFormat('Y-m-d', '2015-02-26'));

        /** @var ApplicationEntity $app */
        $app = null;

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $application) use (&$app) {
                    $app = $application;
                    $application->setId(22);
                    $application->getLicence()->setId(33);
                }
            );

        $result1 = new Result();
        $result1->addId('fee', 44);
        $data = ['id' => 22, 'feeTypeFeeType' => null, 'description' => null];
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result1);

        $result2 = new Result();
        $result2->addMessage('Section updated');
        if ($isInternal) {
            $this->commandHandler->shouldReceive('handleCommand')
                ->with(UpdateApplicationCompletion::class, false)
                ->andReturn($result2)
                ->twice();
        } else {
            $this->commandHandler->shouldReceive('handleCommand')
                ->with(UpdateApplicationCompletion::class, false)
                ->andReturn($result2)
                ->once();
        }

        $result3 = new Result();
        $result3->addMessage('Licence number generated');
        $this->expectedSideEffect(GenerateLicenceNumber::class, ['id' => 22], $result3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 22,
                'licence' => 33,
                'fee' => 44
            ],
            'messages' => [
                'Licence created',
                'Application created',
                'Application Completion created',
                'Application Tracking created',
                'Section updated',
                'Licence number generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ApplicationEntity::class, $app);
        $this->assertInstanceOf(ApplicationCompletion::class, $app->getApplicationCompletion());
        $this->assertInstanceOf(ApplicationTracking::class, $app->getApplicationTracking());
        $this->assertInstanceOf(Licence::class, $app->getLicence());
        $this->assertSame($this->references[Organisation::class][11], $app->getLicence()->getOrganisation());
        $this->assertSame($this->refData[$licenceStatus], $app->getLicence()->getStatus());
        if ($isInternal) {
            $this->assertSame(
                $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                $app->getStatus()
            );
        }
        $this->assertSame($this->refData[$appliedVia], $app->getAppliedVia());

        $this->assertInstanceOf('\DateTime', $app->getReceivedDate());
        $this->assertEquals('2015-01-01', $app->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals('2015-02-26', $app->getTargetCompletionDate()->format('Y-m-d')); // +8 weeks
        $this->assertEquals('Y', $app->getNiFlag());
        $this->assertSame($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL], $app->getLicenceType());
        $this->assertSame($this->refData[Licence::LICENCE_CATEGORY_GOODS_VEHICLE], $app->getGoodsOrPsv());
        $this->assertSame(
            $this->references[TrafficArea::class][TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE],
            $app->getLicence()->getTrafficArea()
        );
        $this->assertSame(0, $app->getLgvDeclarationConfirmation());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('environmentProvider')]
    public function testHandleCommandGb(mixed $isInternal, mixed $isExternal, mixed $licenceStatus, mixed $appliedVia): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->times(4)
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn($isInternal)
            ->shouldReceive('isGranted')
            ->twice()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn($isExternal);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn(\DateTime::createFromFormat('Y-m-d', '2015-02-26'));

        $command = Cmd::create(
            [
                'organisation' => 11,
                'receivedDate' => '2015-01-01',
                'trafficArea' => TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE,
                'niFlag' => 'N',
                'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'lgvDeclarationConfirmation' => 1,
                'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE,
            ]
        );
        /** @var ApplicationEntity $app */
        $app = null;

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $application) use (&$app) {
                    $app = $application;
                    $application->setId(22);
                    $application->getLicence()->setId(33);
                }
            );

        $result1 = new Result();
        $result1->addId('fee', 44);
        $data = ['id' => 22, 'feeTypeFeeType' => null, 'description' => null];
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result1);

        $result2 = new Result();
        $result2->addMessage('Section updated');
        if ($isInternal) {
            $this->commandHandler->shouldReceive('handleCommand')
                ->with(UpdateApplicationCompletion::class, false)
                ->andReturn($result2)
                ->twice();
        } else {
            $this->commandHandler->shouldReceive('handleCommand')
                ->with(UpdateApplicationCompletion::class, false)
                ->andReturn($result2)
                ->once();
        }

        $result3 = new Result();
        $result3->addMessage('Licence number generated');
        $this->expectedSideEffect(GenerateLicenceNumber::class, ['id' => 22], $result3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 22,
                'licence' => 33,
                'fee' => 44
            ],
            'messages' => [
                'Licence created',
                'Application created',
                'Application Completion created',
                'Application Tracking created',
                'Section updated',
                'Licence number generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ApplicationEntity::class, $app);
        $this->assertInstanceOf(ApplicationCompletion::class, $app->getApplicationCompletion());
        $this->assertInstanceOf(ApplicationTracking::class, $app->getApplicationTracking());
        $this->assertInstanceOf(Licence::class, $app->getLicence());
        $this->assertSame($this->references[Organisation::class][11], $app->getLicence()->getOrganisation());
        $this->assertSame($this->refData[$licenceStatus], $app->getLicence()->getStatus());
        if ($isInternal) {
            $this->assertSame(
                $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                $app->getStatus()
            );
        }
        $this->assertSame($this->refData[$appliedVia], $app->getAppliedVia());

        $this->assertInstanceOf('\DateTime', $app->getReceivedDate());
        $this->assertEquals('2015-01-01', $app->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals('2015-02-26', $app->getTargetCompletionDate()->format('Y-m-d')); // +8 weeks
        $this->assertEquals('N', $app->getNiFlag());
        $this->assertSame($this->refData[Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL], $app->getLicenceType());
        $this->assertSame($this->refData[Licence::LICENCE_CATEGORY_GOODS_VEHICLE], $app->getGoodsOrPsv());
        $this->assertSame(
            $this->references[TrafficArea::class][TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE],
            $app->getLicence()->getTrafficArea()
        );
        $this->assertSame($this->refData[RefData::APP_VEHICLE_TYPE_LGV], $app->getVehicleType());
        $this->assertSame(1, $app->getLgvDeclarationConfirmation());
    }

    public static function environmentProvider(): array
    {
        return [
            [true, false, Licence::LICENCE_STATUS_UNDER_CONSIDERATION, ApplicationEntity::APPLIED_VIA_PHONE],
            [false, true, Licence::LICENCE_STATUS_NOT_SUBMITTED, ApplicationEntity::APPLIED_VIA_SELFSERVE]
        ];
    }
}
