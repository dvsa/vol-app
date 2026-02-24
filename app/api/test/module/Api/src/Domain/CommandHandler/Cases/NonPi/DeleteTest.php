<?php

declare(strict_types=1);

/**
 * Create NonPi Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\NonPi;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as NonPiEntity;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Delete as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create NonPi Test
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var DeleteCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DeleteCommandHandler();
        $this->mockRepo('NonPi', NonPi::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 111;

        $data = [
            'id' => $id,
            'version' => 2
        ];

        $command = DeleteCommand::create($data);

        /** @var NonPiEntity $impounding */
        $noteEntity = m::mock(NonPiEntity::class)->makePartial();
        $noteEntity->setId($command->getId());

        /** @var $e NonPiEntity */
        $e = null;

        $this->repoMap['NonPi']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($noteEntity)
            ->shouldReceive('delete')
            ->with(m::type(NonPiEntity::class))
            ->andReturnUsing(
                function (NonPiEntity $ce) use (&$e) {
                    $e = $ce;
                    $e->setId(111);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 111 deleted', $result->getMessages());
    }
}
