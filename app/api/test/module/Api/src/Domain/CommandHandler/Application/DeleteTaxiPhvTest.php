<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteTaxiPhv as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Application\DeleteTaxiPhv as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * DeleteTaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteTaxiPhvTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);

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
            'ids' => [472, 123, 186],
            'licence' => 1,
            'lva' => 'application'
        ];
        $command = Command::create($params);

        $mockLicence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class)->makePartial();
        $mockLicence->setTrafficArea('FOO');
        $mockLicence->shouldReceive('getPrivateHireLicences->isEmpty')->andReturn(true);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $mockApplication->setId(323);
        $mockApplication->setLicence($mockLicence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($mockApplication);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\PrivateHireLicence\DeleteList::class,
            [
                'ids' => $params['ids'],
                'licence' => 1,
                'lva' => 'application'
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('DELETE')
        );

        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Licence\Licence $licence) {
                $this->assertNull($licence->getTrafficArea());
            }
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
        $this->assertSame(
            ['DELETE', 'Licence Traffic Area set to null', 'UPDATE_COMPLETION'],
            $response->getMessages()
        );
    }
}
