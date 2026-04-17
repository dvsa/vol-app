<?php

declare(strict_types=1);

/**
 * Delete For Responsibilities Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\DeleteForResponsibilities;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\DeleteForResponsibilities as Cmd;

/**
 * Delete For Responsibilities Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteForResponsibilitiesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteForResponsibilities();

        $this->mockRepo('TransportManagerApplication', Repository\TransportManagerApplication::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111,
                222
            ]
        ];

        $command = Cmd::create($data);
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);

        /** @var TransportManagerApplication $tma1 */
        $tma1 = m::mock(TransportManagerApplication::class)->makePartial();
        $tma1->setApplication($application);

        /** @var TransportManagerApplication $tma2 */
        $tma2 = m::mock(TransportManagerApplication::class)->makePartial();
        $tma2->setApplication($application);

        $tmas = [
            $tma1,
            $tma2
        ];

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchByIds')
            ->with([111, 222])
            ->andReturn($tmas)
            ->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($tma1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($tma2)
            ->shouldReceive('delete')
            ->once()
            ->with($tma1)
            ->shouldReceive('delete')
            ->once()
            ->with($tma2);

        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $data = [
            'id' => 111,
            'section' => 'transportManagers'
        ];
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id111' => 111,
                'id222' => 222
            ],
            'messages' => [
                'Id 111 deleted',
                'Id 222 deleted',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
