<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterTodo as LetterTodoRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as LetterTodoEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update LetterTodo Test
 */
final class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterTodo', LetterTodoRepo::class);

        parent::setUp();
    }

    private function todoWithCurrentVersion(bool $requiresInput): LetterTodoEntity
    {
        $currentVersion = new LetterTodoVersion();
        $currentVersion->setVersionNumber(1);
        $currentVersion->setRequiresInput($requiresInput);

        $todo = new LetterTodoEntity();
        $todo->setId(5);
        $todo->addVersion($currentVersion);
        $todo->setCurrentVersion($currentVersion);

        return $todo;
    }

    private function update(LetterTodoEntity $todo, array $data): void
    {
        $command = Cmd::create(array_merge(['id' => 5, 'version' => 1, 'todoKey' => 'FI01'], $data));

        $this->repoMap['LetterTodo']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($todo);
        $this->repoMap['LetterTodo']->shouldReceive('save')->with($todo)->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(5, $result->getId('letterTodo'));
    }

    public function testRequiresInputCanBeTicked(): void
    {
        $todo = $this->todoWithCurrentVersion(false);

        $this->update($todo, ['requiresInput' => true]);

        $this->assertTrue($todo->getRequiresInput());
    }

    public function testRequiresInputCanBeUnticked(): void
    {
        $todo = $this->todoWithCurrentVersion(true);

        $this->update($todo, ['requiresInput' => false]);

        $this->assertFalse($todo->getRequiresInput());
    }

    public function testRequiresInputIsLeftAloneWhenNotSent(): void
    {
        $todo = $this->todoWithCurrentVersion(true);

        $this->update($todo, []);

        $this->assertTrue($todo->getRequiresInput());
    }
}
