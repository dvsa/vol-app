<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\CreateForIrhpApplication;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\UpdateIrhpPermitWindow;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdatePeriod as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdatePeriod as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdatePeriodTest extends AbstractCommandHandlerTestCase
{
    public $command;

    public $data = [
        'id' => 100000,
        'irhpPermitStock' => 12
    ];

    public $irhpApplicationEntity;

    public $irhpPermitWindowEntity;

    public $countryEntity;

    public $irhpPermitStockEntity;

    public $irhpPermitApplicationEntity;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitWindowRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);

        $this->irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $this->irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $this->irhpPermitWindowEntity = m::mock(IrhpPermitWindow::class);
        $this->countryEntity = m::mock(Country::class);

        $this->irhpPermitStockEntity = m::mock(IrhpPermitStockEntity::class)->makePartial();
        $this->irhpPermitStockEntity->setCountry($this->countryEntity);

        $this->mockedSmServices = [
            'QaApplicationAnswersClearer' => m::mock(ApplicationAnswersClearer::class),
        ];

        $this->command = Command::create($this->data);

        parent::setUp();
    }

    /**
     * Test creation of IRHP Permit Application - typical first happy path
     *
     */
    public function testHandleCommand(): void
    {
        $this->irhpApplicationEntity->shouldReceive('getId')
            ->once()
            ->andReturn($this->data['id']);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->once()
            ->with($this->data['irhpPermitStock'])
            ->andReturn($this->irhpPermitStockEntity);

        $this->irhpApplicationEntity->shouldReceive('getIrhpPermitApplicationIdForCountry')
            ->once()
            ->with($this->countryEntity)
            ->andReturn(null);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->once()
            ->with($this->data['id'])
            ->andReturn($this->irhpApplicationEntity);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchLastOpenWindowByStockId')
            ->once()
            ->with($this->data['irhpPermitStock'])
            ->andReturn($this->irhpPermitWindowEntity);

        $this->irhpPermitWindowEntity->shouldReceive('getId')
            ->once()
            ->andReturn(5343);

        $cmdResult = new Result();
        $cmdResult->addId('irhpPermitApplication', 333);
        $cmdResult->addMessage('IrhpPermitApplication Created');

        $this->expectedSideEffect(
            CreateForIrhpApplication::class,
            [
                'irhpApplication' => 100000,
                'irhpPermitWindow' => 5343
            ],
            $cmdResult
        );

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [
                'irhpApplication' => $this->data['id'],
                'irhpPermitApplication' => 333
            ],
            'messages' => [
                0 => 'IrhpPermitApplication Created',
                1 => 'IrhpPermitApplication for selected stock period linked',
                2 => 'Period selection completed for IRHP application'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }


    /**
     * Test creation of IRHP Permit Application - typical first happy path
     *
     */
    public function testHandleCommandUpdateWindow(): void
    {
        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->once()
            ->with($this->data['id'])
            ->andReturn($this->irhpApplicationEntity);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->once()
            ->with($this->data['irhpPermitStock'])
            ->andReturn($this->irhpPermitStockEntity);

        $this->irhpApplicationEntity->shouldReceive('getIrhpPermitApplicationIdForCountry')
            ->once()
            ->with($this->countryEntity)
            ->andReturn(3665);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchLastOpenWindowByStockId')
            ->once()
            ->with($this->data['irhpPermitStock'])
            ->andReturn($this->irhpPermitWindowEntity);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('fetchById')
            ->once()
            ->with(3665)
            ->andReturn($this->irhpPermitApplicationEntity);

        $this->irhpPermitApplicationEntity->shouldReceive('getIrhpPermitWindow')
            ->once()
            ->withNoArgs()
            ->andReturn($this->irhpPermitWindowEntity);

        $this->irhpPermitApplicationEntity->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(3665);

        $this->irhpPermitWindowEntity->shouldReceive('getId')
            ->once()
            ->andReturn(5343);

        $this->irhpPermitWindowEntity->shouldReceive('getId')
            ->twice()
            ->andReturn(6643);

        $this->mockedSmServices['QaApplicationAnswersClearer']->shouldReceive('clear')
            ->with($this->irhpPermitApplicationEntity)
            ->once();

        $cmdResult = new Result();
        $cmdResult->addId('irhpPermitApplication', 333);
        $cmdResult->addMessage('IrhpPermitApplication Created');

        $this->expectedSideEffect(
            UpdateIrhpPermitWindow::class,
            [
                'id' => 3665,
                'irhpPermitWindow' => 6643
            ],
            $cmdResult
        );

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [
                'irhpApplication' => $this->data['id'],
                'irhpPermitApplication' => 333
            ],
            'messages' => [
                0 => 'IrhpPermitApplication Created',
                1 => 'IrhpPermitApplication for selected stock period linked',
                2 => 'Period selection completed for IRHP application'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }


    /**
     * Test re-selection of same period. No side-effects, return to selfserve with same values
     *
     */
    public function testHandleCommandSamePeriodExistingIrhpPermitApplication(): void
    {
        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->once()
            ->with($this->data['id'])
            ->andReturn($this->irhpApplicationEntity);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->with($this->data['irhpPermitStock'])
            ->once()
            ->andReturn($this->irhpPermitStockEntity);

        $this->irhpApplicationEntity->shouldReceive('getIrhpPermitApplicationIdForCountry')
            ->once()
            ->with($this->countryEntity)
            ->andReturn(3665);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('fetchById')
            ->once()
            ->with(3665)
            ->andReturn($this->irhpPermitApplicationEntity);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchLastOpenWindowByStockId')
            ->once()
            ->with($this->data['irhpPermitStock'])
            ->andReturn($this->irhpPermitWindowEntity);

        $this->irhpPermitApplicationEntity->shouldReceive('getIrhpPermitWindow')
            ->once()
            ->withNoArgs()
            ->andReturn($this->irhpPermitWindowEntity);

        $this->irhpPermitApplicationEntity->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(3665);

        $this->irhpPermitWindowEntity->shouldReceive('getId')
            ->twice()
            ->andReturn(5343);

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [
                'irhpApplication' => $this->data['id'],
                'irhpPermitApplication' => 3665
            ],
            'messages' => [
                0 => 'IrhpPermitApplication for selected stock period linked',
                1 => 'Period selection completed for IRHP application'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
