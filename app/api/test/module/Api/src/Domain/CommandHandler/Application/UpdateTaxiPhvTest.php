<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateTaxiPhv as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTaxiPhv as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\Application as EntityApplication;

/**
 * UpdateTaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTaxiPhvTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            'TrafficAreaValidator' => m::mock(),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        parent::initReferences();
    }

    public function testHandleCommandNewApplicationTaFromCommand(): void
    {
        $params = [
            'id' => 323,
            'trafficArea' => 'TA',
        ];
        $command = Command::create($params);

        /** @var m\MockInterface|\Dvsa\Olcs\Api\Entity\Licence\Licence $mockLicence */
        $mockLicence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class)->makePartial();
        $mockLicence->setId(210);

        /** @var m\MockInterface|EntityApplication $mockApplication */
        $mockApplication = m::mock(EntityApplication::class)->makePartial();
        $mockApplication->setId(323);
        $mockApplication->setIsVariation(false);
        $mockApplication->setLicence($mockLicence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')
            ->with($mockApplication, 'TA')->once()->andReturn(true);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea::class,
            [
                'id' => 210,
                'trafficArea' => 'TA',
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('UPDATE')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 323,
                'section' => 'taxiPhv',
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('UPDATE_COMPLETION')
        );

        $response = $this->sut->handleCommand($command);

        static::assertSame([], $response->getIds());
        static::assertSame(['UPDATE', 'UPDATE_COMPLETION'], $response->getMessages());
    }

    public function testHandleCommandValidationError(): void
    {
        $params = [
            'id' => 323,
            'trafficArea' => 'TA',
        ];
        $command = Command::create($params);

        /** @var m\MockInterface|EntityApplication $mockApplication */
        $mockApplication = m::mock(EntityApplication::class)->makePartial();
        $mockApplication->setId(323);
        $mockApplication->setIsVariation(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')
            ->with($mockApplication, 'TA')->once()->andReturn(['KEY' => 'MESSAGE']);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandValidationErrorTaFromApplication(): void
    {
        $params = [
            'id' => 323,
        ];
        $command = Command::create($params);

        /** @var m\MockInterface|EntityApplication $mockApplication */
        $mockApplication = m::mock(EntityApplication::class)->makePartial();
        $mockApplication->setId(323);
        $mockApplication->setIsVariation(false);
        $mockApplication->shouldReceive('getTrafficArea->getId')->with()->once()->andReturn('TA2');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')
            ->with($mockApplication, 'TA2')->once()->andReturn(['KEY' => 'MESSAGE']);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
