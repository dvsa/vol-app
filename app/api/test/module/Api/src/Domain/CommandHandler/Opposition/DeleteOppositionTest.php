<?php

declare(strict_types=1);

/**
 * Delete Opposition Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Opposition;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Opposition\DeleteOpposition as DeleteCommandHandler;
use Dvsa\Olcs\Transfer\Command\Opposition\DeleteOpposition as DeleteCommand;
use Dvsa\Olcs\Api\Domain\Repository\Opposition;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as OppositionEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Delete Opposition Test
 */
class DeleteOppositionTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var DeleteCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DeleteCommandHandler();
        $this->mockRepo('Opposition', Opposition::class);

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

        /** @var OppositionEntity $oppositionEntity */
        $oppositionEntity = m::mock(OppositionEntity::class)->makePartial();
        $oppositionEntity->setId($command->getId());

        /** @var OppositionEntity $opposition */
        $opposition = null;

        $this->repoMap['Opposition']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($oppositionEntity)
            ->shouldReceive('delete')
            ->with(m::type(OppositionEntity::class))
            ->andReturnUsing(
                function (OppositionEntity $oe) use (&$opposition) {
                    $opposition = $oe;
                    $opposition->setId(111);
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
