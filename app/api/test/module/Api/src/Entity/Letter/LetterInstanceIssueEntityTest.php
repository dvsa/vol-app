<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterInstanceIssue Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterInstanceIssueEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function entityWithDefault(array $default): Entity
    {
        $version = new LetterIssueVersion();
        $version->setDefaultBodyContent($default);

        $entity = new Entity();
        $entity->setLetterIssueVersion($version);

        return $entity;
    }

    public function testEffectiveContentFallsBackToVersionDefaultWhenNothingGenerated(): void
    {
        $default = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear [[OP_NAME_ONLY]]']]]];
        $entity = $this->entityWithDefault($default);

        $this->assertSame($default, $entity->getEffectiveContent());
    }

    public function testGeneratedContentIsUsedWhenNotEdited(): void
    {
        $generated = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]];
        $entity = $this->entityWithDefault(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear [[OP_NAME_ONLY]]']]]]);
        $entity->setGeneratedContent($generated);

        $this->assertSame($generated, $entity->getEffectiveContent());
    }

    public function testEditedContentWinsOverGeneratedContent(): void
    {
        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear Acme (edited)']]]];
        $entity = $this->entityWithDefault(['blocks' => []]);
        $entity->setGeneratedContent(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]]);
        $entity->setEditedContentFromArray($edited);

        $this->assertSame($edited, $entity->getEffectiveContent());
    }

    public function testGeneratedContentDoesNotCountAsEdited(): void
    {
        $entity = $this->entityWithDefault(['blocks' => []]);
        $entity->setGeneratedContent(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Dear ACME LTD']]]]);

        $this->assertFalse($entity->hasBeenEdited());
    }

    private function otherIssue(): Entity
    {
        $version = new LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther(true);
        $version->setRequiresInput(true);
        $version->setDefaultBodyContent(json_encode(['time' => 1, 'blocks' => [['id' => 'seed', 'type' => 'paragraph', 'data' => ['text' => 'Describe the issue here.']]]]));

        $entity = new Entity();
        $entity->setLetterIssueVersion($version);

        return $entity;
    }

    public function testOtherIssueHeadingIsTheCaseworkersOnceWritten(): void
    {
        $entity = $this->otherIssue();
        $this->assertTrue($entity->isOther());
        $this->assertSame('Other issue', $entity->getHeading());

        $entity->setEditedHeading('Vehicle storage');
        $this->assertSame('Vehicle storage', $entity->getHeading());

        $entity->setEditedHeading('  ');
        $this->assertSame('Other issue', $entity->getHeading());
    }

    public function testStandardIssueIgnoresAnEditedHeading(): void
    {
        $version = new LetterIssueVersion();
        $version->setHeading('Bank statements too old');
        $entity = new Entity();
        $entity->setLetterIssueVersion($version);
        $entity->setEditedHeading('Something else');

        $this->assertFalse($entity->isOther());
        $this->assertSame('Bank statements too old', $entity->getHeading());
    }

    public function testOtherIssueIsPendingUntilHeadingAndBodyAreWritten(): void
    {
        $entity = $this->otherIssue();
        $this->assertTrue($entity->isInputPending(), 'nothing written');

        $entity->setEditedHeading('Vehicle storage');
        $this->assertTrue($entity->isInputPending(), 'heading only');

        $entity->setEditedContentFromArray(['time' => 2, 'blocks' => [['id' => 'new', 'type' => 'paragraph', 'data' => ['text' => 'Describe the issue here. ']]]]);
        $this->assertTrue($entity->isInputPending(), 'placeholder saved unchanged');

        $entity->setEditedContentFromArray(['blocks' => [['id' => 'x', 'type' => 'paragraph', 'data' => ['text' => 'Lorry parked on verge']]]]);
        $this->assertFalse($entity->isInputPending());
        $this->assertTrue($entity->hasBeenEdited());

        $entity->setEditedHeading(null);
        $this->assertTrue($entity->isInputPending(), 'body only');
    }

    public function testOtherIssueWithOnlyAHeadingCountsAsEdited(): void
    {
        $entity = $this->otherIssue();
        $this->assertFalse($entity->hasBeenEdited());

        $entity->setEditedHeading('Vehicle storage');
        $this->assertTrue($entity->hasBeenEdited());
    }

    public function testStandardIssueInputPendingIsUnchanged(): void
    {
        $version = new LetterIssueVersion();
        $version->setRequiresInput(true);
        $entity = new Entity();
        $entity->setLetterIssueVersion($version);
        $this->assertTrue($entity->isInputPending());

        $entity->setEditedContentFromArray(['blocks' => [['id' => 'x', 'type' => 'paragraph', 'data' => ['text' => 'Anything']]]]);
        $this->assertFalse($entity->isInputPending());

        $version->setRequiresInput(false);
        $this->assertFalse((new Entity())->setLetterIssueVersion($version)->isInputPending());
    }
}
