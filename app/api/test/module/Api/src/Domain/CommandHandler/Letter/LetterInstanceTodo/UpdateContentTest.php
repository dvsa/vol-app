<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo\UpdateContent as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceTodo as LetterInstanceTodoRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo as LetterInstanceTodoEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\UpdateContent as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateContent LetterInstanceTodo Test
 */
final class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    /** The standing wording, shared by every letter that pulls this to-do in. */
    private const STANDING = [
        'blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Standing wording']]],
    ];

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceTodo', LetterInstanceTodoRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $todoId = 42;
        $editedDescription = '{"blocks":[{"type":"paragraph","data":{"text":"Hello world"}}],"version":"2.28.2"}';
        $expectedArray = json_decode($editedDescription, true);

        $command = Cmd::create([
            'id' => $todoId,
            'editedDescription' => $editedDescription,
            'version' => 1,
        ]);

        $entity = m::mock(LetterInstanceTodoEntity::class)->makePartial();
        $entity->setId($todoId);

        $entity->shouldReceive('setEditedDescriptionFromArray')
            ->with($expectedArray)
            ->once()
            ->andReturnSelf();

        $this->repoMap['LetterInstanceTodo']->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($entity);

        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')
            ->with($entity)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($todoId, $result->getId('letterInstanceTodo'));
        $this->assertStringContainsString('updated successfully', (string) $result->getMessages()[0]);
    }

    public function testItDoesNotTouchTheSharedWording(): void
    {
        // The safety property of the whole feature: an edit is scoped to one letter, so every
        // other letter using this to-do keeps the standing wording. Uses a real entity rather
        // than a mock so the write actually happens and can be checked.
        $version = new LetterTodoVersion();
        $version->setDescription(self::STANDING);

        $entity = new LetterInstanceTodoEntity();
        $entity->setId(42);
        $entity->setLetterTodoVersion($version);

        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Edited for this operator']]]];
        $command = Cmd::create([
            'id' => 42,
            'editedDescription' => json_encode($edited),
            'version' => 1,
        ]);

        $this->repoMap['LetterInstanceTodo']->shouldReceive('fetchUsingId')->once()->andReturn($entity);
        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')->once();

        $this->sut->handleCommand($command);

        $this->assertSame($edited, $entity->getEffectiveDescription());
        $this->assertSame(
            self::STANDING,
            $entity->getLetterTodoVersion()->getDescription(),
            'the shared letter_todo_version must never be written by an instance-scoped edit'
        );
    }
}
