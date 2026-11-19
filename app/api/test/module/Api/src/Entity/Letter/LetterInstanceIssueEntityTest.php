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

    /**
     * Review: the editor opens on the placeholder with its grabs filled in at generation, so that
     * saved unchanged is still the placeholder
     */
    public function testOtherIssuePlaceholderWithItsGrabsFilledInIsStillThePlaceholder(): void
    {
        $entity = $this->otherIssue();
        $entity->getLetterIssueVersion()->setDefaultBodyContent(json_encode(['blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => 'Describe the issue with licence [[LICENCE_NUMBER]] here.']],
        ]]));
        $filledIn = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Describe the issue with licence OB1234567 here.']]]];
        $entity->setGeneratedContent($filledIn);
        $entity->setEditedHeading('Vehicle storage');

        $entity->setEditedContentFromArray(['time' => 3] + $filledIn);
        $this->assertTrue($entity->isInputPending());

        $entity->setEditedContentFromArray(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Lorry parked on verge']]]]);
        $this->assertFalse($entity->isInputPending());
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

    public function testAnUnwrittenOtherIssueIsNamedByItsType(): void
    {
        $type = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType();
        $type->setName('Finances');
        $entity = $this->otherIssue();
        $entity->getLetterIssueVersion()->setLetterIssueType($type);

        $this->assertSame('Other issue (Finances)', $entity->getDisplayName());
        $this->assertSame('Other issue', $entity->getHeading(), 'the letter still prints the plain heading');

        $entity->setEditedHeading('Vehicle storage');
        $this->assertSame('Vehicle storage', $entity->getDisplayName());
    }

    public function testAStandardIssueIsNamedByItsHeading(): void
    {
        $version = new LetterIssueVersion();
        $version->setHeading('Bank statements too old');
        $entity = new Entity();
        $entity->setLetterIssueVersion($version);

        $this->assertSame('Bank statements too old', $entity->getDisplayName());
    }

    public function testTheGeneralOtherIssueIsNotNamedAfterItsOwnType(): void
    {
        $type = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType();
        $type->setName('Other issues');
        $type->setCode(\Dvsa\Olcs\Api\Entity\Letter\LetterIssueType::CODE_OTHER);
        $entity = $this->otherIssue();
        $entity->getLetterIssueVersion()->setLetterIssueType($type);

        $this->assertSame('Other issue', $entity->getDisplayName());
    }

    private function paragraph(string $text): array
    {
        return ['blocks' => [['type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    /**
     * Saving an editor stores its text whether or not anything was typed, so an issue can hold
     * edited content that says just what it started with
     */
    public function testAnIssueSavedAsItStartedHasNotChangedWording(): void
    {
        $entity = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $entity->setGeneratedContent($this->paragraph('Dear Smith & Sons'));
        // as an editor hands it back: its own ids and time, and the "&" written as markup
        $entity->setEditedContentFromArray([
            'time' => 1763100000000,
            'blocks' => [['id' => 'a1B2', 'type' => 'paragraph', 'data' => ['text' => 'Dear Smith &amp; Sons']]],
            'version' => '2.30.8',
        ]);

        $this->assertTrue($entity->hasBeenEdited());
        $this->assertFalse($entity->hasChangedWording());
    }

    public function testAnIssueWhoseWordsWereEditedHasChangedWording(): void
    {
        $entity = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $entity->setGeneratedContent($this->paragraph('Dear Smith & Sons'));
        $entity->setEditedContentFromArray($this->paragraph('Dear Mr Smith'));

        $this->assertTrue($entity->hasChangedWording());
    }

    public function testAnIssueNeverSavedHasNotChangedWording(): void
    {
        $entity = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $entity->setGeneratedContent($this->paragraph('Dear Smith & Sons'));

        $this->assertFalse($entity->hasChangedWording());
    }

    /**
     * A letter made before grabs were filled in at generation has nothing generated. Its editor
     * opened on the library wording, tokens and all.
     */
    public function testWithNothingGeneratedTheWordingIsComparedWithTheLibrary(): void
    {
        $same = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $same->setEditedContentFromArray($this->paragraph('Dear [[OP_NAME_ONLY]]'));

        $changed = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $changed->setEditedContentFromArray($this->paragraph('Dear [[OP_NAME_ONLY]], thank you for your call'));

        $this->assertFalse($same->hasChangedWording());
        $this->assertTrue($changed->hasChangedWording());
    }

    /**
     * It is the words that count. The same words laid out another way (in bold, or as the list
     * an editor rewrites in its own shape) are not a change.
     */
    public function testTheSameWordsLaidOutAnotherWayAreNotChangedWording(): void
    {
        $entity = $this->entityWithDefault(['blocks' => []]);
        $entity->setGeneratedContent(['blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => 'Send us:']],
            ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => ['the lease', 'the bank statement']]],
        ]]);
        $entity->setEditedContentFromArray(['blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => '<b>Send us:</b>']],
            ['type' => 'list', 'data' => ['style' => 'unordered', 'meta' => [], 'items' => [
                ['content' => 'the lease', 'meta' => [], 'items' => []],
                ['content' => 'the bank statement', 'meta' => [], 'items' => []],
            ]]],
        ]]);

        $this->assertFalse($entity->hasChangedWording());
    }

    public function testEditedContentThatCannotBeReadIsNotChangedWording(): void
    {
        $entity = $this->entityWithDefault($this->paragraph('Dear [[OP_NAME_ONLY]]'));
        $entity->setEditedContent('{not json');

        $this->assertFalse($entity->hasChangedWording());
    }
}
