<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LetterTodo as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Mockery as m;

/**
 * Saving a to-do creates a new version whenever a versioned field changes.
 */
final class LetterTodoTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    private function todoWithCurrentVersion(): LetterTodo
    {
        $currentVersion = new LetterTodoVersion();
        $currentVersion->setVersionNumber(2);
        $currentVersion->setName('Bank statements');
        $currentVersion->setDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Upload them']]]]);
        $currentVersion->setHelpText('Last three months');
        $currentVersion->setRequiresInput(false);

        $todo = new LetterTodo();
        $todo->addVersion($currentVersion);
        $todo->setCurrentVersion($currentVersion);

        return $todo;
    }

    private function expectNewVersionSaved(LetterTodo $todo): void
    {
        $this->em->expects('persist')->with(m::type(LetterTodoVersion::class));
        $this->em->expects('persist')->with($todo);
        $this->em->expects('flush');
    }

    public function testFlaggingRequiresInputCreatesANewVersion(): void
    {
        $todo = $this->todoWithCurrentVersion();
        $todo->setRequiresInput(true);

        $this->expectNewVersionSaved($todo);

        $this->sut->save($todo);

        $this->assertSame(3, $todo->getCurrentVersion()->getVersionNumber());
        $this->assertTrue($todo->getCurrentVersion()->getRequiresInput());
    }

    /**
     * The admin form has no name field, so a new version has to carry the name over or it ends up
     * null and the preview sidebar falls back to the key.
     */
    public function testEditingATodoKeepsItsName(): void
    {
        $todo = $this->todoWithCurrentVersion();
        $todo->setDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'New wording']]]]);

        $this->expectNewVersionSaved($todo);

        $this->sut->save($todo);

        $this->assertSame(3, $todo->getCurrentVersion()->getVersionNumber());
        $this->assertSame('Bank statements', $todo->getCurrentVersion()->getName());
    }

    public function testSavingWithoutChangesKeepsTheCurrentVersion(): void
    {
        $todo = $this->todoWithCurrentVersion();
        $currentVersion = $todo->getCurrentVersion();
        $todo->setRequiresInput(false);

        $this->em->expects('persist')->with($todo);
        $this->em->expects('flush');

        $this->sut->save($todo);

        $this->assertSame($currentVersion, $todo->getCurrentVersion());
    }
}
