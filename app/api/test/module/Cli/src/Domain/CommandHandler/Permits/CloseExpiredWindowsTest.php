<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitWindow\Close as CloseWindowCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CloseExpiredWindows as CloseExpiredWindowsCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\CloseExpiredWindows as CloseExpiredWindowsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Close expired windows Test
 */
class CloseExpiredWindowsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseExpiredWindowsHandler();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $params = ['since' => '-1 day'];
        $w1Id = 10;
        $w2Id = 11;

        $command = CloseExpiredWindowsCmd::create($params);

        $w1 = m::mock(PermitWindowEntity::class);
        $w1->shouldReceive('getId')
            ->andReturn($w1Id);

        $w2 = m::mock(PermitWindowEntity::class);
        $w2->shouldReceive('getId')
            ->andReturn($w2Id);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchWindowsToBeClosed')
            ->with(m::type(\DateTime::class), $params['since'])
            ->andReturn([$w1, $w2]);

        $this->expectedSideEffect(
            CloseWindowCmd::class,
            [
                'id' => $w1Id,
            ],
            (new Result())->addMessage('Window 1 has been cancelled')
        );
        $this->expectedSideEffect(
            CloseWindowCmd::class,
            [
                'id' => $w2Id,
            ],
            (new Result())->addMessage('Window 2 has been cancelled')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Window 1 has been cancelled',
                'Window 2 has been cancelled',
                'Expired windows have been closed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenNoWindowsToBeClosed(): void
    {
        $params = ['since' => '-1 day'];

        $command = CloseExpiredWindowsCmd::create($params);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchWindowsToBeClosed')
            ->with(m::type(\DateTime::class), $params['since'])
            ->andReturn([]);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'No expired windows found',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
