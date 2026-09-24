<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterTodo as LetterTodoRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as LetterTodoEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create LetterTodo Test
 */
final class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterTodo', LetterTodoRepo::class);

        parent::setUp();
    }

    private function saveAndCapture(Cmd $command): LetterTodoEntity
    {
        $saved = null;

        $this->repoMap['LetterTodo']->shouldReceive('save')
            ->with(m::type(LetterTodoEntity::class))
            ->once()
            ->andReturnUsing(function (LetterTodoEntity $entity) use (&$saved) {
                $entity->setId(99);
                $saved = $entity;
            });

        $result = $this->sut->handleCommand($command);

        $this->assertSame(99, $result->getId('letterTodo'));

        return $saved;
    }

    public function testHandleCommand(): void
    {
        $description = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Upload them']]]];

        $saved = $this->saveAndCapture(Cmd::create([
            'todoKey' => 'FI01',
            'description' => $description,
            'helpText' => 'Last three months',
            'requiresInput' => true,
        ]));

        $this->assertSame('FI01', $saved->getTodoKey());
        $this->assertSame($description, $saved->getDescription());
        $this->assertSame('Last three months', $saved->getHelpText());
        $this->assertTrue($saved->getRequiresInput());
    }

    public function testRequiresInputIsOffUnlessTicked(): void
    {
        $command = Cmd::create(['todoKey' => 'FI01']);

        $this->assertFalse($command->getRequiresInput());
        $this->assertFalse($this->saveAndCapture($command)->getRequiresInput());
    }
}
