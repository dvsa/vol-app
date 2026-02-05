<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateFull as CreateHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationUpdater;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CheckedValueUpdater;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateFull as CreateCmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateMultipleNoOfPermits;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationPath;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Irhp Application test
 */
class UpdateFullTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsCheckableCheckedValueUpdater' => m::mock(CheckedValueUpdater::class),
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
            'PermitsBilateralInternalApplicationUpdater' => m::mock(ApplicationUpdater::class),
        ];

        parent::setUp();
    }

    public function testHandleCommandBilateral(): void
    {
        $permitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL;
        $licenceId = 2;

        $cmdData = [
            'id' => 4,
            'type' => $permitTypeId,
            'licence' => $licenceId,
            'corCertificateNumber' => 'UKCR32/00123',
            'dateReceived' => '2019-01-03',
            'declaration' => 0,
            'countries' => ['DE', 'FR', 'NL'],
            'permitsRequired' => [
                'DE' => [
                    2019 => 2,
                    2020 => 2
                ],
                'FR' => [
                    2019 => 2,
                    2020 => 2
                ],
                'NL' => [
                    2020 => 2
                ],
            ],
            'checked' => 1
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with(4)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $irhpApplicationEntity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(4);

        $this->mockedSmServices['PermitsCheckableCheckedValueUpdater']->shouldReceive('updateIfRequired')
            ->with($irhpApplicationEntity, $cmdData['checked'])
            ->once();

        $irhpApplicationEntity->shouldReceive('updateCorCertificateNumber')
            ->once()
            ->with($cmdData['corCertificateNumber']);

        $irhpApplicationEntity->shouldReceive('updateDateReceived')
            ->once()
            ->with($cmdData['dateReceived']);

        $irhpApplicationEntity->shouldReceive('resetSectionCompletion')
            ->twice();

        $irhpApplicationEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $irhpApplicationEntity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($permitTypeId);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($irhpApplicationEntity, EventHistoryTypeEntity::IRHP_APPLICATION_UPDATED)
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

        $this->mockedSmServices['PermitsBilateralInternalApplicationUpdater']->shouldReceive('update')
            ->with($irhpApplicationEntity, $command->getPermitsRequired())
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 4,
            ],
            'messages' => [
                0 => 'section updated',
                1 => 'IRHP Application updated successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandMultilateral(): void
    {
        $permitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL;
        $licenceId = 2;

        $cmdData = [
            'id' => 44,
            'type' => $permitTypeId,
            'licence' => $licenceId,
            'corCertificateNumber' => 'UKCR32/00123',
            'dateReceived' => '2020-01-03',
            'declaration' => 0,
            'countries' => ['DE', 'FR', 'NL'],
            'permitsRequired' => [
                '2020' => 10,
                '2021' => 12
            ],
            'checked' => 1
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with(44)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $irhpApplicationEntity->shouldReceive('getId')
            ->times(2)
            ->andReturn(44);

        $this->mockedSmServices['PermitsCheckableCheckedValueUpdater']->shouldReceive('updateIfRequired')
            ->with($irhpApplicationEntity, $cmdData['checked'])
            ->once();

        $irhpApplicationEntity->shouldReceive('updateCorCertificateNumber')
            ->once()
            ->with($cmdData['corCertificateNumber']);

        $irhpApplicationEntity->shouldReceive('updateDateReceived')
            ->once()
            ->with($cmdData['dateReceived']);

        $irhpApplicationEntity->shouldReceive('resetSectionCompletion')
            ->twice();

        $irhpApplicationEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $irhpApplicationEntity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($permitTypeId);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($irhpApplicationEntity, EventHistoryTypeEntity::IRHP_APPLICATION_UPDATED)
            ->once();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('refresh')
            ->twice()
            ->andReturnSelf();

        $result2 = new Result();
        $result2->addMessage('section updated');
        $sideEffectData = [
            'id' => 44,
            'permitsRequired' => $command->getPermitsRequired()
        ];
        $this->expectedSideEffect(UpdateMultipleNoOfPermits::class, $sideEffectData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 44,
            ],
            'messages' => [
                0 => 'section updated',
                1 => 'IRHP Application updated successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandQandA')]
    public function testHandleCommandQandA(mixed $irhpPermitTypeId): void
    {
        $licenceId = 2;

        $cmdData = [
            'id' => 34,
            'type' => $irhpPermitTypeId,
            'licence' => $licenceId,
            'corCertificateNumber' => 'UKCR32/00123',
            'dateReceived' => '2090-01-03',
            'declaration' => 0,
            'postData' => ['key' => 'val'],
            'checked' => 1
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with(34)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $irhpApplicationEntity->shouldReceive('getId')
            ->times(2)
            ->andReturn(34);

        $this->mockedSmServices['PermitsCheckableCheckedValueUpdater']->shouldReceive('updateIfRequired')
            ->with($irhpApplicationEntity, $cmdData['checked'])
            ->once();

        $irhpApplicationEntity->shouldReceive('updateCorCertificateNumber')
            ->once()
            ->with($cmdData['corCertificateNumber']);

        $irhpApplicationEntity->shouldReceive('updateDateReceived')
            ->once()
            ->with($cmdData['dateReceived']);

        $irhpApplicationEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($irhpApplicationEntity, EventHistoryTypeEntity::IRHP_APPLICATION_UPDATED)
            ->once();

        $result2 = new Result();

        $sideEffectData = [
            'id' => 34,
            'postData' => ['key' => 'val']
        ];
        $this->expectedSideEffect(SubmitApplicationPath::class, $sideEffectData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 34,
            ],
            'messages' => [
                0 => 'IRHP Application updated successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandQandA')]
    public function testHandleCommandQandAWithDeclaration(mixed $irhpPermitTypeId): void
    {
        $licenceId = 2;

        $cmdData = [
            'id' => 34,
            'type' => $irhpPermitTypeId,
            'licence' => $licenceId,
            'corCertificateNumber' => 'UKCR32/00123',
            'dateReceived' => '2090-01-03',
            'declaration' => 1,
            'postData' => ['key' => 'val'],
            'checked' => 1
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with(34)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $irhpApplicationEntity->shouldReceive('getId')
            ->times(2)
            ->andReturn(34);

        $this->mockedSmServices['PermitsCheckableCheckedValueUpdater']->shouldReceive('updateIfRequired')
            ->with($irhpApplicationEntity, $cmdData['checked'])
            ->once();

        $irhpApplicationEntity
            ->shouldReceive('updateCorCertificateNumber')
            ->once()
            ->with($cmdData['corCertificateNumber'])
            ->shouldReceive('updateDateReceived')
            ->once()
            ->with($cmdData['dateReceived'])
            ->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->once()
            ->andReturn(true)
            ->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->once()
            ->andReturnTrue()
            ->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->shouldReceive('resetSectionCompletion')
            ->withNoArgs()
            ->once()
            ->shouldReceive('makeDeclaration')
            ->withNoArgs()
            ->once();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->with($irhpApplicationEntity)
            ->times(3)
            ->shouldReceive('refresh')
            ->with($irhpApplicationEntity)
            ->once();

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($irhpApplicationEntity, EventHistoryTypeEntity::IRHP_APPLICATION_UPDATED)
            ->once();

        $result2 = new Result();

        $sideEffectData = [
            'id' => 34,
            'postData' => ['key' => 'val']
        ];
        $this->expectedSideEffect(SubmitApplicationPath::class, $sideEffectData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irhpApplication' => 34,
            ],
            'messages' => [
                0 => 'IRHP Application updated successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpTestHandleCommandQandA(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL]
        ];
    }

    public function testHandleCommandUnsupportedNoneQandAPermitType(): void
    {
        $id = 44;
        $permitTypeId = 'unsupported';

        $cmdData = [
            'id' => $id,
            'type' => $permitTypeId,
        ];

        $command = CreateCmd::create($cmdData);

        $irhpApplicationEntity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpApplicationEntity);

        $irhpApplicationEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $irhpApplicationEntity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($permitTypeId);

        $irhpApplicationEntity->shouldReceive('resetSectionCompletion')
            ->withNoArgs()
            ->once();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('refresh')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturnSelf();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported permit type unsupported');

        $this->sut->handleCommand($command);
    }
}
