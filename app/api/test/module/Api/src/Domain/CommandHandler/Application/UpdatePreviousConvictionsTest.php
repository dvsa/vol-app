<?php

declare(strict_types=1);

/**
 * Update Previous Convictions Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdatePreviousConvictions;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePreviousConvictions as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;

/**
 * Update Previous Convictions Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UpdatePreviousConvictionsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePreviousConvictions();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'prevConviction' => 'N',
            'convictionsConfirmation' => 'Y',
            'version' => 1
        ];

        $command = Cmd::create($data);

        $application = m::mock(ApplicationEntity::class)
            ->makePartial()
            ->shouldReceive('setPrevConviction')
            ->with('N')
            ->once()
            ->shouldReceive('setConvictionsConfirmation')
            ->with('Y')
            ->once()
            ->shouldReceive('getId')
            ->andReturn(627)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'convictionsPenalties'],
            new Result()
        );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Application saved successfully']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}
