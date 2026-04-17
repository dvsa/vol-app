<?php

declare(strict_types=1);

/**
 * Delete For Responsibilities Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\Command\Licence\TmNominatedTask;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerLicence\DeleteForResponsibilities;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\DeleteForResponsibilities as Cmd;

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

        $this->mockRepo('TransportManagerLicence', Repository\TransportManagerLicence::class);

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

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(123);

        /** @var TransportManagerLicence $tml1 */
        $tml1 = m::mock(TransportManagerLicence::class)->makePartial();
        $tml1->setLicence($licence);

        /** @var TransportManagerLicence $tml2 */
        $tml2 = m::mock(TransportManagerLicence::class)->makePartial();
        $tml2->setLicence($licence);

        $tmls = [
            $tml1,
            $tml2
        ];

        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchByIds')
            ->with([111, 222])
            ->andReturn($tmls)
            ->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($tml1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($tml2)
            ->shouldReceive('delete')
            ->once()
            ->with($tml1)
            ->shouldReceive('delete')
            ->once()
            ->with($tml2);

        $result1 = new Result();
        $result1->addMessage('TmNominatedTask');
        $data = [
            'ids' => [
                123 => 123
            ]
        ];
        $this->expectedSideEffect(TmNominatedTask::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id111' => 111,
                'id222' => 222
            ],
            'messages' => [
                'Id 111 deleted',
                'Id 222 deleted',
                'TmNominatedTask'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
