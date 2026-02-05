<?php

declare(strict_types=1);

/**
 * Create Conviction Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Conviction\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Conviction;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as ConvictionEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Delete as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Conviction Test
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
        $this->mockRepo('Conviction', Conviction::class);

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

        /** @var ConvictionEntity $impounding */
        $noteEntity = m::mock(ConvictionEntity::class)->makePartial();
        $noteEntity->setId($command->getId());

        /** @var $conviction ConvictionEntity */
        $conviction = null;

        $this->repoMap['Conviction']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($noteEntity)
            ->shouldReceive('delete')
            ->with(m::type(ConvictionEntity::class))
            ->andReturnUsing(
                function (ConvictionEntity $ce) use (&$conviction) {
                    $conviction = $ce;
                    $conviction->setId(111);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 111 deleted', $result->getMessages());
    }
}
