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
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceTodo', LetterInstanceTodoRepo::class);
        $this->mockRepo('LetterInstanceIssue', LetterInstanceIssueRepo::class);

        parent::setUp();
    }

    public function testAddsAnEmptyCustomTodoAtTheEndOfTheOtherIssue(): void
    {
        $issue = $this->issueOnLetter(true);
        $existing = new LetterInstanceTodo();
        $existing->setLetterTodoVersion(new LetterTodoVersion());
        $existing->setDisplayOrder(7);
        $issue->addLetterInstanceTodo($existing);
        $issue->getLetterInstance()->addLetterInstanceTodo($existing);

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
        $this->assertSame(8, $saved->getDisplayOrder());
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
}
