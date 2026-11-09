<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo\DeleteCustom as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceTodo as LetterInstanceTodoRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\DeleteCustom as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Deleting a caseworker-written to-do (VOL-7735)
 */
final class DeleteCustomTest extends AbstractCommandHandlerTestCase
{
    use CustomTodoTestTrait;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceTodo', LetterInstanceTodoRepo::class);

        parent::setUp();
    }

    private function todoOn(bool $custom, string $status = LetterInstance::STATUS_DRAFT): LetterInstanceTodo
    {
        $issue = $this->issueOnLetter(true, $status);
        $todo = new LetterInstanceTodo();
        $todo->setId(99);
        if (!$custom) {
            $todo->setLetterTodoVersion(new LetterTodoVersion());
        }
        $issue->addLetterInstanceTodo($todo);
        $issue->getLetterInstance()->addLetterInstanceTodo($todo);

        return $todo;
    }

    public function testDeletesACustomTodo(): void
    {
        $todo = $this->todoOn(true);
        $issue = $todo->getLetterInstanceIssue();
        $letter = $todo->getLetterInstance();
        $command = Cmd::create(['id' => 99]);

        $this->repoMap['LetterInstanceTodo']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($todo);
        $this->repoMap['LetterInstanceTodo']->shouldReceive('delete')->with($todo)->once();

        $this->sut->handleCommand($command);

        $this->assertFalse($issue->getLetterInstanceTodos()->contains($todo));
        $this->assertFalse($letter->getLetterInstanceTodos()->contains($todo));
    }

    public function testCannotDeleteALibraryTodo(): void
    {
        $this->repoMap['LetterInstanceTodo']->shouldReceive('fetchUsingId')->andReturn($this->todoOn(false));
        $this->repoMap['LetterInstanceTodo']->shouldReceive('delete')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 99]));
    }

    public function testCannotChangeASentLetter(): void
    {
        $this->repoMap['LetterInstanceTodo']->shouldReceive('fetchUsingId')
            ->andReturn($this->todoOn(true, LetterInstance::STATUS_SENT));
        $this->repoMap['LetterInstanceTodo']->shouldReceive('delete')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 99]));
    }
}
