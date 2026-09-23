<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterTodo Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterTodoEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function todoWithCurrentVersion(bool $requiresInput, ?string $name): Entity
    {
        $currentVersion = new LetterTodoVersion();
        $currentVersion->setVersionNumber(3);
        $currentVersion->setRequiresInput($requiresInput);
        $currentVersion->setName($name);

        $todo = new Entity();
        $todo->addVersion($currentVersion);
        $todo->setCurrentVersion($currentVersion);

        return $todo;
    }

    public function testRequiresInputFallsBackToTheCurrentVersion(): void
    {
        $this->assertTrue($this->todoWithCurrentVersion(true, null)->getRequiresInput());
    }

    public function testRequiresInputIsFalseWithoutAVersion(): void
    {
        $this->assertFalse(new Entity()->getRequiresInput());
    }

    public function testRequiresInputSetOnTheTodoWinsOverTheCurrentVersion(): void
    {
        $todo = $this->todoWithCurrentVersion(true, null);
        $todo->setRequiresInput(false);

        $this->assertFalse($todo->getRequiresInput());
    }

    public function testNameFallsBackToTheCurrentVersion(): void
    {
        $this->assertSame('Bank statements', $this->todoWithCurrentVersion(false, 'Bank statements')->getName());
    }

    public function testNameSetOnTheTodoWinsOverTheCurrentVersion(): void
    {
        $todo = $this->todoWithCurrentVersion(false, 'Bank statements');
        $todo->setName('Latest bank statements');

        $this->assertSame('Latest bank statements', $todo->getName());
    }

    public function testCreateNewVersionCarriesRequiresInputAndName(): void
    {
        $newVersion = $this->todoWithCurrentVersion(true, 'Bank statements')->createNewVersion();

        $this->assertSame(4, $newVersion->getVersionNumber());
        $this->assertTrue($newVersion->getRequiresInput());
        $this->assertSame('Bank statements', $newVersion->getName());
    }
}
