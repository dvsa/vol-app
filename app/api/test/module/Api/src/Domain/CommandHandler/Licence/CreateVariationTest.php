<?php

declare(strict_types=1);

/**
 * Create Variation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateVariation;
use Dvsa\Olcs\Api\Domain\Repository\Sla;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Variation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateVariation();
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Sla', Sla::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedSmServices[SlaCalculatorInterface::class] = m::mock(SlaCalculator::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED,
            LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            LicenceEntity::LICENCE_STATUS_VALID,
            ApplicationEntity::APPLIED_VIA_PHONE,
            ApplicationEntity::APPLIED_VIA_SELFSERVE,
            Application::VARIATION_TYPE_DIRECTOR_CHANGE,
        ];

        parent::initReferences();
    }

    public function testHandleCommandInternalFull(): void
    {
        $data = [
            'id' => 111,
            'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            'receivedDate' => '2015-01-01',
            'feeRequired' => 'Y',
            'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);
        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $expectedTargetCompletionDate = new \DateTime();
        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $trafficArea = m::mock(TrafficArea::class)->makePartial();
        $trafficArea->expects('getIsNi')->withNoArgs()->andReturn(false);
        $licence->shouldReceive('getTrafficArea')->withNoArgs()->andReturn($trafficArea);
        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) use ($expectedTargetCompletionDate) {
                    $app->setId(222);
                    $this->assertEquals('2015-01-01', $app->getReceivedDate()->format('Y-m-d'));
                    $this->assertEquals($expectedTargetCompletionDate, $app->getTargetCompletionDate());
                    $this->assertSame(
                        $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        $app->getStatus()
                    );
                    $this->assertNull($app->getVariationType());
                }
            );

        $data = ['id' => 222, 'section' => 'typeOfLicence'];
        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $data = ['id' => 222, 'feeTypeFeeType' => FeeType::FEE_TYPE_VAR, 'task' => null];
        $result2 = new Result();
        $result2->addMessage('CreateFee');
        $this->expectedSideEffect(CreateFee::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created',
                'UpdateApplicationCompletion',
                'CreateFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInternalWithVariationType(): void
    {
        $data = [
            'id' => 111,
            'variationType' => Application::VARIATION_TYPE_DIRECTOR_CHANGE,
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);

        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) {
                    $app->setId(222);
                    $this->assertSame(
                        $this->refData[Application::VARIATION_TYPE_DIRECTOR_CHANGE],
                        $app->getVariationType()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInternalNoFee(): void
    {
        $data = [
            'id' => 111,
            'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            'receivedDate' => '2015-01-01',
            'feeRequired' => 'N',
            'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);
        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $expectedTargetCompletionDate = new \DateTime();
        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->andReturn($expectedTargetCompletionDate);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $trafficArea = m::mock(TrafficArea::class)->makePartial();
        $trafficArea->expects('getIsNi')->withNoArgs()->andReturn(false);
        $licence->shouldReceive('getTrafficArea')->withNoArgs()->andReturn($trafficArea);

        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) {
                    $app->setId(222);
                    $this->assertEquals('2015-01-01', $app->getReceivedDate()->format('Y-m-d'));
                    $this->assertSame(
                        $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        $app->getStatus()
                    );
                }
            );

        $data = ['id' => 222, 'section' => 'typeOfLicence'];
        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInternalNoLicenceType(): void
    {
        $data = [
            'id' => 111,
            'receivedDate' => '2015-01-01',
            'feeRequired' => 'N',
            'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);
        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $expectedTargetCompletionDate = new \DateTime();
        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $trafficArea = m::mock(TrafficArea::class)->makePartial();
        $trafficArea->expects('getIsNi')->withNoArgs()->andReturn(false);
        $licence->shouldReceive('getTrafficArea')->withNoArgs()->andReturn($trafficArea);

        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) {
                    $app->setId(222);
                    $this->assertEquals('2015-01-01', $app->getReceivedDate()->format('Y-m-d'));
                    $this->assertSame(
                        $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        $app->getStatus()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInternalNoReceivedDate(): void
    {
        $data = [
            'id' => 111,
            'feeRequired' => 'N',
            'appliedVia' => ApplicationEntity::APPLIED_VIA_PHONE
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->shouldNotReceive('applySla');

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);

        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) {
                    $app->setId(222);
                    $this->assertNull($app->getReceivedDate());
                    $this->assertSame(
                        $this->refData[ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        $app->getStatus()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandSelfserveNoReceivedDate(): void
    {
        $data = [
            'id' => 111,
            'feeRequired' => 'N'
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false)
            ->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);

        $licence->shouldReceive('canHaveVariation')->andReturn(true);

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->shouldNotReceive('applySla');

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('save')
            ->with(m::type(ApplicationEntity::class))
            ->andReturnUsing(
                function (ApplicationEntity $app) {
                    $app->setId(222);
                    $this->assertNull($app->getReceivedDate());
                    $this->assertSame(
                        $this->refData[ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
                        $app->getStatus()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 222
            ],
            'messages' => [
                'Variation created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandVariationException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $data = [
            'id' => 111,
            'feeRequired' => 'N'
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $command = \Dvsa\Olcs\Transfer\Command\Licence\CreateVariation::create($data);

        /** @var m\Mock|LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);

        $licence->shouldReceive('canHaveVariation')->andReturn(false);

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->shouldNotReceive('applySla');

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->sut->handleCommand($command);
    }
}
