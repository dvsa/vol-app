<?php

declare(strict_types=1);

/**
 * Create Note Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Processing\Note;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Delete as DeleteCommand;
use Dvsa\Olcs\Api\Domain\Repository\Note;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity;

/**
 * Create Note Test
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
        $this->mockRepo('Note', Note::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 111;

        $data = [
            'id' => $id,
        ];

        $command = DeleteCommand::create($data);

        /** @var NoteEntity $impounding */
        $noteEntity = m::mock(NoteEntity::class)->makePartial();
        $noteEntity->setId($command->getId());

        /** @var $note NoteEntity */
        $note = null;

        $this->repoMap['Note']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($noteEntity)
            ->shouldReceive('delete')
            ->with(m::type(NoteEntity::class))
            ->andReturnUsing(
                function (NoteEntity $noteEntity) use (&$note) {
                    $note = $noteEntity;
                    $note->setId(111);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Note deleted', $result->getMessages());
    }
}
