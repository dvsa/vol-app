<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\CreateFull as CreateHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationUpdater;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CreateFull as CreateCmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateMultipleNoOfPermits;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create Irhp Application test
 */
class CreateFullTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(CreateHandler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        $this->mockedSmServices = [
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
            'PermitsBilateralInternalApplicationUpdater' => m::mock(ApplicationUpdater::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            IrhpPermitType::class => [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL => m::mock(IrhpPermitType::class),
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL => m::mock(IrhpPermitType::class)
            ],
            Licence::class => [
                2 => m::mock(Licence::class),
            ],
            IrhpApplication::class => [
                4 => m::mock(IrhpApplication::class),
            ],
        ];

        $this->refData = [
            IrhpInterface::SOURCE_INTERNAL,
            IrhpInterface::STATUS_NOT_YET_SUBMITTED,
        ];

        parent::initReferences();
    }

    public function testHandleCommandBilateral(): void
    {
        $permitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL;
        $licenceId = 2;

        $cmdData = [
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
            'dateReceived' => '2019-01-01',
            'declaration' => 0,
            'countries' => ['DE', 'FR', 'NL'],
            'permitsRequired' => [
                'DE' => [
                    2019 => 1,
                    2020 => 1
                ],
                'FR' => [
                    2019 => 1,
                    2020 => 1
                ],
                'NL' => [
                    2020 => 1
                ],
            ]
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with(m::type(IrhpApplication::class))
            ->once()
            ->andReturnUsing(
                function (IrhpApplication $app) {
                    $app->setId(4);
                }
            );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with(m::type(IrhpApplication::class), EventHistoryTypeEntity::IRHP_APPLICATION_CREATED)
            ->once();

        $result1 = new Result();
        $result1->addMessage('section updated');
        $sideEffectData = [
            'id' => 4,
            'countries' => array_keys($command->getPermitsRequired())
        ];
        $this->expectedSideEffect(UpdateCountries::class, $sideEffectData, $result1);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('refresh')
            ->twice()
            ->andReturnSelf();

        $result2 = new Result();
        $result2->addMessage('section updated');
        $sideEffectData = [
            'id' => 4,
            'permitsRequired' => $command->getPermitsRequired()
        ];

        $this->mockedSmServices['PermitsBilateralInternalApplicationUpdater']->shouldReceive('update')
            ->with(m::type(IrhpApplication::class), $command->getPermitsRequired())
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 4,
            ],
            'messages' => [
                0 => 'section updated',
                1 => 'IRHP Application created successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandMultilateral(): void
    {
        $permitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL;
        $licenceId = 2;

        $cmdData = [
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
            'dateReceived' => '2020-01-01',
            'declaration' => 0,
            'permitsRequired' => [
                '2020' => 10,
                '2021' => 12
            ]
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplication = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with(m::type(IrhpApplication::class))
            ->once()
            ->andReturnUsing(
                function (IrhpApplication $app) use (&$irhpApplication) {
                    $irhpApplication = $app;
                    $app->setId(4);
                }
            );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with(m::type(IrhpApplication::class), EventHistoryTypeEntity::IRHP_APPLICATION_CREATED)
            ->once();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('refresh')
            ->twice()
            ->andReturnSelf();

        $openWindow1 = m::mock(IrhpPermitWindow::class);
        $openWindow2 = m::mock(IrhpPermitWindow::class);
        $openWindows = [$openWindow1, $openWindow2];

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByType')
            ->once()
            ->andReturnUsing(
                function ($type, $now) use ($permitTypeId, $openWindows) {
                    $this->assertEquals($permitTypeId, $type);

                    $this->assertInstanceOf(DateTime::class, $now);
                    $this->assertEquals(
                        date('Y-m-d'),
                        $now->format('Y-m-d')
                    );

                    return $openWindows;
                }
            );

        $this->repoMap['IrhpPermitApplication']->shouldReceive('save')
            ->andReturn($irhpApplication);

        $result2 = new Result();
        $result2->addMessage('section updated');
        $sideEffectData = [
            'id' => 4,
            'permitsRequired' => $command->getPermitsRequired()
        ];
        $this->expectedSideEffect(UpdateMultipleNoOfPermits::class, $sideEffectData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 4,
            ],
            'messages' => [
                0 => 'section updated',
                1 => 'IRHP Application created successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithDeclaration(): void
    {
        $permitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL;
        $licenceId = 2;
        $dateReceived = '2020-01-01';

        $cmdData = [
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
            'dateReceived' => $dateReceived,
            'declaration' => 1,
            'permitsRequired' => [
                '2020' => 10,
                '2021' => 12
            ]
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplication = m::mock(IrhpApplication::class)->makePartial();
        $irhpApplication->shouldReceive('resetSectionCompletion')
            ->withNoArgs()
            ->times(3)
            ->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->shouldReceive('makeDeclaration')
            ->withNoArgs()
            ->once();

        $this->sut->shouldReceive('createNewIrhpApplication')
            ->with(
                $this->references[IrhpPermitType::class][$permitTypeId],
                $this->references[Licence::class][$licenceId],
                $dateReceived
            )
            ->once()
            ->andReturn($irhpApplication);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with(m::type(IrhpApplication::class))
            ->times(3)
            ->andReturnUsing(
                function (IrhpApplication $app) use (&$irhpApplication) {
                    $irhpApplication = $app;
                    $app->setId(4);
                }
            );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with(m::type(IrhpApplication::class), EventHistoryTypeEntity::IRHP_APPLICATION_CREATED)
            ->once();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('refresh')
            ->times(3)
            ->andReturnSelf();

        $openWindow1 = m::mock(IrhpPermitWindow::class);
        $openWindow2 = m::mock(IrhpPermitWindow::class);
        $openWindows = [$openWindow1, $openWindow2];

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByType')
            ->once()
            ->andReturn($openWindows);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('save')
            ->andReturn($irhpApplication);

        $result2 = new Result();
        $result2->addMessage('section updated');
        $sideEffectData = [
            'id' => 4,
            'permitsRequired' => $command->getPermitsRequired()
        ];
        $this->expectedSideEffect(UpdateMultipleNoOfPermits::class, $sideEffectData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 4,
            ],
            'messages' => [
                0 => 'section updated',
                1 => 'IRHP Application created successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoPermitTypeFound(): void
    {
        $permitTypeId = 999;
        $licenceId = 2;

        $cmdData = [
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
        ];

        $command = CreateCmd::create($cmdData);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Permit type not found');

        $this->sut->handleCommand($command);
    }
}
