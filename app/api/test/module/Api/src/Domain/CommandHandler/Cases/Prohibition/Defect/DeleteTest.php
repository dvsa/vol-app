<?php

declare(strict_types=1);

/**
 * Create Prohibition\Defect Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect;
use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as ProhibitionDefectEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Delete as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Prohibition Defect Delete Test
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
        $this->mockRepo('ProhibitionDefect', ProhibitionDefect::class);

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

        /** @var ProhibitionDefectEntity $impounding */
        $noteEntity = m::mock(ProhibitionDefectEntity::class)->makePartial();
        $noteEntity->setId($command->getId());

        /** @var $conviction ProhibitionDefectEntity */
        $conviction = null;

        $this->repoMap['ProhibitionDefect']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($noteEntity)
            ->shouldReceive('delete')
            ->with(m::type(ProhibitionDefectEntity::class))
            ->andReturnUsing(
                function (ProhibitionDefectEntity $ce) use (&$conviction) {
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
