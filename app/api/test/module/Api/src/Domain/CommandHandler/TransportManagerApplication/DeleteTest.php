<?php

declare(strict_types=1);

/**
 * DeleteTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\Delete as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Delete as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication;
use Mockery as m;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManagerApplication', TransportManagerApplication::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['ids' => [863, 234]]);

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class),
            true
        );
        $application->setId(12);
        $tma1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma1->setApplication($application);
        $tma2 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma2->setApplication($application);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(863)->once()->andReturn($tma1);
        $this->repoMap['TransportManagerApplication']->shouldReceive('delete')->with($tma1)->once();
        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(234)->once()->andReturn($tma2);
        $this->repoMap['TransportManagerApplication']->shouldReceive('delete')->with($tma2)->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 12,
                'section' => 'transportManagers'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'Transport Manager Application ID 863 deleted',
                'Transport Manager Application ID 234 deleted'
            ],
            $result->getMessages()
        );
    }
}
