<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\CreateDefaultIrhpPermitApplications;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create as CreateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Irhp Application test
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('IrhpPermitType', IrhpPermitTypeRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            IrhpApplication::class => [
                4 => m::mock(IrhpApplication::class),
            ],
        ];

        $this->refData = [
            IrhpInterface::SOURCE_SELFSERVE,
            IrhpInterface::SOURCE_INTERNAL,
            IrhpInterface::STATUS_NOT_YET_SUBMITTED,
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $fromInternal, mixed $source, mixed $withStockId): void
    {
        $irhpAppId = 11;
        $permitTypeId = 22;
        $licenceId = 33;
        $stockId = 44;

        $cmdData = [
            'irhpPermitStock' => $withStockId ? $stockId : null,
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
            'fromInternal' => $fromInternal,
        ];

        $command = CreateCmd::create($cmdData);

        $permitType = m::mock(IrhpPermitType::class);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchById')
            ->once()
            ->with($permitTypeId)
            ->andReturn($permitType);

        $permitStock = m::mock(IrhpPermitStock::class);
        $permitStock->shouldReceive('getId')->times($withStockId ? 0 : 1)->withNoArgs()->andReturn($stockId);

        //no stock id then need to get the window
        $permitWindow = m::mock(IrhpPermitWindow::class);
        $permitWindow->shouldReceive('getIrhpPermitStock')
            ->times($withStockId ? 0 : 1)
            ->withNoArgs()
            ->andReturn($permitStock);

        //no stock id then need to get the window
        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchLastOpenWindowByIrhpPermitType')
            ->times($withStockId ? 0 : 1)
            ->with($permitTypeId, m::type(\DateTime::class))
            ->andReturn($permitWindow);

        //of there is already a stock id we get it directly
        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->times($withStockId ? 1 : 0)
            ->with($stockId)
            ->andReturn($permitStock);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('canMakeIrhpApplication')->once()->with($permitStock)->andReturnTrue();

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->once()
            ->with($licenceId)
            ->andReturn($licence);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpApplication::class))
            ->andReturnUsing(
                function ($irhpApplication) use ($source, $permitType, $licence, $irhpAppId) {
                    $this->assertSame(
                        $this->refData[$source],
                        $irhpApplication->getSource()
                    );

                    $this->assertSame(
                        $this->refData[IrhpInterface::STATUS_NOT_YET_SUBMITTED],
                        $irhpApplication->getStatus()
                    );

                    $this->assertSame(
                        $permitType,
                        $irhpApplication->getIrhpPermitType()
                    );

                    $this->assertSame(
                        $licence,
                        $irhpApplication->getLicence()
                    );

                    $this->assertEquals(
                        date('Y-m-d'),
                        $irhpApplication->getDateReceived(true)->format('Y-m-d')
                    );

                    $irhpApplication->setId($irhpAppId);

                    return;
                }
            );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with(m::type(IrhpApplication::class), EventHistoryTypeEntity::IRHP_APPLICATION_CREATED)
            ->once();

        $sideEffectMessage = 'Message from CreateDefaultIrhpPermitApplications';

        $sideEffectResult = new Result();
        $sideEffectResult->addMessage($sideEffectMessage);

        $this->expectedSideEffect(
            CreateDefaultIrhpPermitApplications::class,
            [
                'id' => $irhpAppId,
                'irhpPermitStock' => $stockId,
            ],
            $sideEffectResult
        );

        $expectedResult = [
            'id' => [
                'irhpApplication' => $irhpAppId,
            ],
            'messages' => [
                $sideEffectMessage,
                'IRHP Application created successfully'
            ]
        ];

        self::assertEquals($expectedResult, $this->sut->handleCommand($command)->toArray());
    }

    public static function dpTestHandleCommand(): array
    {
        return
            [
                [true, IrhpInterface::SOURCE_INTERNAL, false],
                [true, IrhpInterface::SOURCE_INTERNAL, true],
                [false, IrhpInterface::SOURCE_SELFSERVE, true],
                [false, IrhpInterface::SOURCE_SELFSERVE, false],
            ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpLicenceNotEligible')]
    public function testHandleCommandLicenceNotEligible(mixed $withStockId): void
    {
        $permitDescription = 'permit description';
        $permitTypeId = 22;
        $licenceId = 33;
        $licNo = 'OB1234567';
        $stockId = 44;

        $cmdData = [
            'irhpPermitStock' => $withStockId ? $stockId : null,
            'irhpPermitType' => $permitTypeId,
            'licence' => $licenceId,
        ];

        $permitType = m::mock(IrhpPermitType::class);
        $permitType->shouldReceive('getName->getDescription')->once()->withNoArgs()->andReturn($permitDescription);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchById')
            ->once()
            ->with($permitTypeId)
            ->andReturn($permitType);

        $permitStock = m::mock(IrhpPermitStock::class);
        $permitStock->shouldReceive('getId')->times($withStockId ? 0 : 1)->withNoArgs()->andReturn($stockId);

        //no stock id then need to get the window
        $permitWindow = m::mock(IrhpPermitWindow::class);
        $permitWindow->shouldReceive('getIrhpPermitStock')
            ->times($withStockId ? 0 : 1)
            ->withNoArgs()
            ->andReturn($permitStock);

        //no stock id then need to get the window
        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchLastOpenWindowByIrhpPermitType')
            ->times($withStockId ? 0 : 1)
            ->with($permitTypeId, m::type(\DateTime::class))
            ->andReturn($permitWindow);

        //of there is already a stock id we get it directly
        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->times($withStockId ? 1 : 0)
            ->with($stockId)
            ->andReturn($permitStock);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('canMakeIrhpApplication')->once()->with($permitStock)->andReturnFalse();
        $licence->shouldReceive('getId')->once()->withNoArgs()->andReturn($licenceId);
        $licence->shouldReceive('getLicNo')->once()->withNoArgs()->andReturn($licNo);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->once()
            ->with($licenceId)
            ->andReturn($licence);

        $command = CreateCmd::create($cmdData);

        $msg = sprintf(CreateHandler::LICENCE_INVALID_MSG, $licenceId, $licNo, $permitDescription, $stockId);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage($msg);

        $this->sut->handleCommand($command);
    }

    public static function dpLicenceNotEligible(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
