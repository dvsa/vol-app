<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterInstanceSection Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterInstanceSectionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function entityWithDefault(array $default): Entity
    {
        $version = new LetterSectionVersion();
        $version->setDefaultContent($default);

        $entity = new Entity();
        $entity->setLetterSectionVersion($version);

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

    /**
     * A letter's section for wording that depends on a letter choice, like the interim paragraph:
     * the section has a catch-all, used when no other wording applies, and wording for when
     * Interim is ticked. The letter gets $wording through whichever of the two is asked for.
     */
    private function sectionWithConditionalWording(array|string|null $wording, bool $choiceWasMade = false): Entity
    {
        $catchAll = new LetterSectionVariant();
        $forTheChoice = new LetterSectionVariant();
        $forTheChoice->setLetterChoice(new LetterChoice());

        $section = new LetterSection();
        $section->addVariant($catchAll);
        $section->addVariant($forTheChoice);

        $version = new LetterSectionVersion();
        $version->setDefaultContent($wording);
        $version->setLetterSectionVariant($choiceWasMade ? $forTheChoice : $catchAll);

        $entity = new Entity();
        $entity->setLetterSectionVersion($version);

        return $entity;
    }

    /**
     * The interim paragraph is only in the letter when Interim is ticked. When it isn't, the
     * letter gets the section's empty catch-all: it prints nothing, so there's nothing to offer
     * for editing.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('emptyWording')]
    public function testAnEmptyCatchAllOfConditionalWordingHasNothingToEdit(mixed $wording): void
    {
        $this->assertTrue($this->sectionWithConditionalWording($wording)->hasNothingToEdit());
    }

    public static function emptyWording(): \Iterator
    {
        yield 'no blocks' => [['time' => 1783057825048, 'blocks' => [], 'version' => '2.31.0']];
        // how the admin screens have stored it: the JSON as a string
        yield 'no blocks, stored as a string' => ['{"time":1783057825048,"blocks":[],"version":"2.31.0"}'];
        yield 'only an empty paragraph' => [['blocks' => [['type' => 'paragraph', 'data' => ['text' => '&nbsp;']]]]];
        yield 'nothing at all' => [null];
    }

    public function testACatchAllWithWordingHasSomethingToEdit(): void
    {
        $wording = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'We will write to you about an interim']]]];

        $this->assertFalse($this->sectionWithConditionalWording($wording)->hasNothingToEdit());

        $generated = $this->sectionWithConditionalWording(['blocks' => []]);
        $generated->setGeneratedContent($wording);
        $this->assertFalse($generated->hasNothingToEdit());
    }

    /**
     * A section with only the one wording, left empty, is there for the caseworker to write in
     * when they need to. Hiding it would leave no way to add to it.
     */
    public function testASectionLeftBlankForTheCaseworkerIsStillOffered(): void
    {
        $onlyWording = new LetterSectionVariant();
        $section = new LetterSection();
        $section->addVariant($onlyWording);

        // wording for a choice that an admin has since deleted doesn't make it conditional
        $deleted = new LetterSectionVariant();
        $deleted->setLetterChoice(new LetterChoice());
        $deleted->setDeletedDate(new \DateTime());
        $section->addVariant($deleted);

        $version = new LetterSectionVersion();
        $version->setDefaultContent(['blocks' => []]);
        $version->setLetterSectionVariant($onlyWording);
        $entity = new Entity();
        $entity->setLetterSectionVersion($version);

        $this->assertFalse($entity->hasNothingToEdit());
    }

    /**
     * Wording that was picked for this letter stays on offer even if an admin left it empty:
     * only the catch-all means "nothing applied"
     */
    public function testEmptyWordingThatWasPickedForTheLetterIsStillOffered(): void
    {
        $this->assertFalse($this->sectionWithConditionalWording(['blocks' => []], true)->hasNothingToEdit());
    }

    public function testAnEmptySectionThatAsksForInputIsStillOffered(): void
    {
        $entity = $this->sectionWithConditionalWording(['blocks' => []]);
        $entity->getLetterSectionVersion()->setRequiresInput(true);

        $this->assertFalse($entity->hasNothingToEdit());
    }

    /**
     * Once a caseworker has saved the section it stays on offer, even if they cleared it, so
     * they can get back to what they wrote
     */
    public function testASectionTheCaseworkerHasSavedIsStillOffered(): void
    {
        $entity = $this->sectionWithConditionalWording(['blocks' => []]);
        $entity->setEditedContentFromArray(['time' => 1783057825048, 'blocks' => [], 'version' => '2.31.0']);

        $this->assertFalse($entity->hasNothingToEdit());
    }
}
