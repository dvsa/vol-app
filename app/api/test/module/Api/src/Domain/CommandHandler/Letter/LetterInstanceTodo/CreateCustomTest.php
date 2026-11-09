<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo\CreateCustom as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceIssue as LetterInstanceIssueRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceTodo as LetterInstanceTodoRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\CreateCustom as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Adding a caseworker-written to-do to an Other issue (VOL-7735)
 */
final class CreateCustomTest extends AbstractCommandHandlerTestCase
{
    use CustomTodoTestTrait;

    public function setUp(): void
    {
        $this->mockedSmServices = [
            \Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer::class => new \Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer(),
        ];
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceTodo', LetterInstanceTodoRepo::class);
        $this->mockRepo('LetterInstanceIssue', LetterInstanceIssueRepo::class);

        parent::setUp();
    }

    /**
     * A library to-do the given issue brings, already on the letter
     */
    private function libraryTodoOn(\Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue $issue, int $todoId, int $displayOrder): LetterInstanceTodo
    {
        $letterTodo = new \Dvsa\Olcs\Api\Entity\Letter\LetterTodo();
        $letterTodo->setId($todoId);
        $version = new LetterTodoVersion();
        $version->setLetterTodo($letterTodo);
        $letterTodo->setCurrentVersion($version);
        $link = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo();
        $link->setLetterTodoVersion($version);
        $issue->getLetterIssueVersion()->addLetterIssueTodo($link);

        $todo = new LetterInstanceTodo();
        $todo->setLetterTodoVersion($version);
        $todo->setDisplayOrder($displayOrder);
        $issue->addLetterInstanceTodo($todo);
        $issue->getLetterInstance()->addLetterInstanceTodo($todo);

        return $todo;
    }

    public function testAddsAnEmptyCustomTodoAtTheEndOfTheOtherIssue(): void
    {
        $issue = $this->issueOnLetter(true);
        $existing = $this->libraryTodoOn($issue, 5, 7);

        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchById')->with(10)->once()->andReturn($issue);
        $saved = null;
        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')->once()->andReturnUsing(
            function (LetterInstanceTodo $todo) use (&$saved): void {
                $todo->setId(99);
                $saved = $todo;
            }
        );

        $result = $this->sut->handleCommand(Cmd::create(['letterInstanceIssue' => 10]));

        $this->assertInstanceOf(LetterInstanceTodo::class, $saved);
        $this->assertTrue($saved->isCustom());
        $this->assertSame($issue, $saved->getLetterInstanceIssue());
        $this->assertTrue($issue->getLetterInstance()->getLetterInstanceTodos()->contains($saved));
        $this->assertSame(1, $saved->getDisplayOrder(), 'after the issue\'s own to-do, numbered in letter order');
        $this->assertSame(0, $existing->getDisplayOrder());
        $this->assertSame(99, $result->getId('letterInstanceTodo'));
    }

    public function testCannotAddACustomTodoToAStandardIssue(): void
    {
        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchById')->andReturn($this->issueOnLetter(false));
        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['letterInstanceIssue' => 10]));
    }

    public function testCannotChangeASentLetter(): void
    {
        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchById')
            ->andReturn($this->issueOnLetter(true, LetterInstance::STATUS_SENT));
        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['letterInstanceIssue' => 10]));
    }

    /**
     * Review: a new to-do is numbered straight after its own issue's to-dos, not after the whole
     * letter's, so lists show it in letter order straight away.
     */
    public function testANewTodoIsNumberedInLetterOrder(): void
    {
        $issue = $this->issueOnLetter(true);
        $issue->setDisplayOrder(0);
        $letter = $issue->getLetterInstance();
        $later = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue();
        $later->setLetterIssueVersion(new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion());
        $later->setDisplayOrder(1);
        $letter->addLetterInstanceIssue($later);
        $laterTodo = $this->libraryTodoOn($later, 6, 0);

        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchById')->andReturn($issue);
        $saved = null;
        $this->repoMap['LetterInstanceTodo']->shouldReceive('save')->once()->andReturnUsing(
            function (LetterInstanceTodo $todo) use (&$saved): void {
                $saved = $todo;
            }
        );

        $this->sut->handleCommand(Cmd::create(['letterInstanceIssue' => 10]));

        $this->assertLessThan($laterTodo->getDisplayOrder(), $saved->getDisplayOrder());
    }
}
