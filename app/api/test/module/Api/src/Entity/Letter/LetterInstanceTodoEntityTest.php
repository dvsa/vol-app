<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterInstanceTodo Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterInstanceTodoEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function entityWithDefault(array $default): Entity
    {
        $version = new LetterTodoVersion();
        $version->setDescription($default);

        $entity = new Entity();
        $entity->setLetterTodoVersion($version);

        return $entity;
    }

    public function testEffectiveContentFallsBackToVersionDefaultWhenNothingGenerated(): void
    {
        $default = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear [[OP_NAME_ONLY]]']]]];
        $entity = $this->entityWithDefault($default);

        $this->assertSame($default, $entity->getEffectiveDescription());
    }

    public function testGeneratedContentIsUsedWhenNotEdited(): void
    {
        $generated = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]];
        $entity = $this->entityWithDefault(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear [[OP_NAME_ONLY]]']]]]);
        $entity->setGeneratedDescription($generated);

        $this->assertSame($generated, $entity->getEffectiveDescription());
    }

    public function testEditedContentWinsOverGeneratedContent(): void
    {
        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear Acme (edited)']]]];
        $entity = $this->entityWithDefault(['blocks' => []]);
        $entity->setGeneratedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]]);
        $entity->setEditedDescriptionFromArray($edited);

        $this->assertSame($edited, $entity->getEffectiveDescription());
    }

    public function testRequiresInputComesFromTheTodoVersion(): void
    {
        $version = new LetterTodoVersion();
        $version->setRequiresInput(true);

        $entity = new Entity();
        $entity->setLetterTodoVersion($version);

        $this->assertTrue($entity->requiresInput());
    }

    public function testRequiresInputIsFalseByDefault(): void
    {
        $entity = new Entity();
        $entity->setLetterTodoVersion(new LetterTodoVersion());

        $this->assertFalse($entity->requiresInput());
    }

    public function testRequiresInputIsFalseWithoutAVersion(): void
    {
        $this->assertFalse(new Entity()->requiresInput());
    }

    public function testGeneratedContentDoesNotCountAsEdited(): void
    {
        $entity = $this->entityWithDefault(['blocks' => []]);
        $entity->setGeneratedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]]);

        $this->assertFalse($entity->hasBeenEdited());
    }

    public function testCustomTodoIsPendingUntilWritten(): void
    {
        $entity = new Entity();
        $this->assertTrue($entity->isCustom());
        $this->assertTrue($entity->isInputPending());

        $entity->setEditedDescriptionFromArray(['blocks' => [['id' => 'x', 'type' => 'paragraph', 'data' => ['text' => '<p> </p>&nbsp;']]]]);
        $this->assertTrue($entity->isInputPending(), 'blank paragraph');

        $entity->setEditedDescriptionFromArray(['blocks' => [['id' => 'x', 'type' => 'paragraph', 'data' => ['text' => 'Send the lease']]]]);
        $this->assertFalse($entity->isInputPending());
    }

    public function testLibraryTodoInputPendingIsUnchanged(): void
    {
        $version = new LetterTodoVersion();
        $version->setRequiresInput(true);
        $entity = new Entity();
        $entity->setLetterTodoVersion($version);

        $this->assertFalse($entity->isCustom());
        $this->assertTrue($entity->isInputPending());

        $entity->setEditedDescriptionFromArray(['blocks' => [['id' => 'x', 'type' => 'paragraph', 'data' => ['text' => 'Done']]]]);
        $this->assertFalse($entity->isInputPending());

        $version->setRequiresInput(false);
        $this->assertFalse((new Entity())->setLetterTodoVersion($version)->isInputPending());
    }

    public function testCustomTodosAreNamedByTheirPlaceUnderTheirIssue(): void
    {
        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther(true);
        $issue = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue();
        $issue->setLetterIssueVersion($version);
        $issue->setEditedHeading('Vehicle storage');

        $library = new Entity();
        $library->setLetterTodoVersion(new LetterTodoVersion());
        $library->setDisplayOrder(1);
        $second = new Entity();
        $second->setDisplayOrder(5);
        $first = new Entity();
        $first->setDisplayOrder(3);
        $issue->addLetterInstanceTodo($library);
        $issue->addLetterInstanceTodo($second);
        $issue->addLetterInstanceTodo($first);

        $this->assertSame(1, $first->getCustomIndex());
        $this->assertSame(2, $second->getCustomIndex());
        $this->assertSame('Vehicle storage: to-do 2', $second->getCustomLabel());
        $this->assertNull($library->getCustomIndex());
        $this->assertNull($library->getCustomLabel());
    }

    public function testACustomTodoUnderAnUnwrittenOtherIsNamedByItsType(): void
    {
        $type = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType();
        $type->setName('Finances');
        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther(true);
        $version->setLetterIssueType($type);
        $issue = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue();
        $issue->setLetterIssueVersion($version);
        $todo = new Entity();
        $issue->addLetterInstanceTodo($todo);

        $this->assertSame('Other issue (Finances): to-do 1', $todo->getCustomLabel());
    }
}
