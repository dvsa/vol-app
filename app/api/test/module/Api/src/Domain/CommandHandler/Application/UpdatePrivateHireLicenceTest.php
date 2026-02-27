<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdatePrivateHireLicence as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePrivateHireLicence as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateTaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdatePrivateHireLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices['TrafficAreaValidator'] = m::mock();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'id' => 323,
            'privateHireLicence' => 654,
            'version' => 21,
            'privateHireLicenceNo' => 'TOPDOG 1',
            'councilName' => 'Leeds',
            'address' => [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
            ],
            'licence' => 1,
            'lva' => 'application'
        ];
        $command = Command::create($params);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $mockApplication->setId(323);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreasWithPostcode')
            ->with($mockApplication, 'S1 4QT')->once()->andReturn(true);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\PrivateHireLicence\Update::class,
            [
                'id' => 654,
                'version' => 21,
                'privateHireLicenceNo' => $params['privateHireLicenceNo'],
                'councilName' => $params['councilName'],
                'address' => $params['address'],
                'licence' => 1,
                'lva' => 'application'
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

        $this->assertSame([], $response->getIds());
        $this->assertSame(['UPDATE', 'UPDATE_COMPLETION'], $response->getMessages());
    }

    public function testHandleCommandValidation(): void
    {
        $params = [
            'id' => 323,
            'privateHireLicence' => 654,
            'version' => 21,
            'privateHireLicenceNo' => 'TOPDOG 1',
            'councilName' => 'Leeds',
            'address' => [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
            ],
            'licence' => 1,
            'lva' => 'application'
        ];
        $command = Command::create($params);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $mockApplication->setId(323);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreasWithPostcode')
            ->with($mockApplication, 'S1 4QT')->once()->andReturn(['CODE' => 'MESSAGE']);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->sut->handleCommand($command);
    }
}
