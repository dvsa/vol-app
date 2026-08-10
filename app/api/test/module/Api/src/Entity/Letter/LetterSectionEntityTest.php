<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * LetterSection Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterSectionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Helper to create a variant with given conditions
     */
    private function createVariant(
        ?RefData $goodsOrPsv = null,
        ?bool $isVariation = null,
        ?bool $isNi = null,
        ?LetterChoice $letterChoice = null
    ): LetterSectionVariant {
        $variant = new LetterSectionVariant();
        $variant->setGoodsOrPsv($goodsOrPsv);
        $variant->setIsVariation($isVariation);
        $variant->setIsNi($isNi);
        $variant->setLetterChoice($letterChoice);

        return $variant;
    }

    // ---------------------------------------------------------------
    // getDefaultVariant() tests
    // ---------------------------------------------------------------

    public function testGetDefaultVariantReturnsVariantWithAllNullConditions(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant(); // all null conditions
        $section->addVariant($defaultVariant);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $conditionedVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($conditionedVariant);

        $result = $section->getDefaultVariant();

        $this->assertSame($defaultVariant, $result);
    }

    public function testGetDefaultVariantReturnsNullWhenNoVariants(): void
    {
        $section = new Entity();

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant::class, $section->getDefaultVariant());
    }

    public function testGetDefaultVariantReturnsNullWhenNoDefaultExists(): void
    {
        $section = new Entity();

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $conditionedVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($conditionedVariant);

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant::class, $section->getDefaultVariant());
    }

    // ---------------------------------------------------------------
    // getVariantForContext() tests
    // ---------------------------------------------------------------

    public function testGetVariantForContextReturnsConditionedVariantWhenContextMatches(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant(); // all null
        $section->addVariant($defaultVariant);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $gvVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($gvVariant);

        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => false,
            'isNi' => false,
            'selectedChoiceIds' => [],
        ]);

        // The conditioned variant should be returned (not the default)
        $this->assertSame($gvVariant, $result);
    }

    public function testGetVariantForContextFallsBackToDefaultWhenNoConditionedVariantMatches(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant(); // all null
        $section->addVariant($defaultVariant);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $gvVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($gvVariant);

        // Context is PSV, so the GV variant doesn't match
        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_psv',
            'isVariation' => false,
            'isNi' => false,
            'selectedChoiceIds' => [],
        ]);

        // Falls back to the default variant
        $this->assertSame($defaultVariant, $result);
    }

    public function testGetVariantForContextReturnsNullWhenNoVariantsAtAll(): void
    {
        $section = new Entity();

        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
        ]);

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant::class, $result);
    }

    public function testGetVariantForContextConditionedVariantWinsOverDefault(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant(); // all null - matches everything
        $section->addVariant($defaultVariant);

        $niVariant = $this->createVariant(isNi: true);
        $section->addVariant($niVariant);

        // Context is NI, both default and NI variant match, but conditioned should win
        $result = $section->getVariantForContext([
            'isNi' => true,
        ]);

        $this->assertSame($niVariant, $result);
        $this->assertNotSame($defaultVariant, $result);
    }

    public function testGetVariantForContextWithMultipleConditionedVariantsReturnsMostSpecificMatch(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant();
        $section->addVariant($defaultVariant);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $gvVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($gvVariant);

        // Second conditioned variant also matching GV + variation
        $gvVariationVariant = $this->createVariant(goodsOrPsv: $gvRefData, isVariation: true);
        $section->addVariant($gvVariationVariant);

        // Both conditioned variants match. The GV+variation one pins down more conditions, so it is
        // the wording an admin wrote specifically for this case and it must beat the broader GV one.
        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'isNi' => false,
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($gvVariationVariant, $result);
    }

    public function testGetVariantForContextIgnoresDeletedVariants(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant();
        $section->addVariant($defaultVariant);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $deletedVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $deletedVariant->setDeletedDate(new \DateTime('2026-07-02 07:26:19'));
        $section->addVariant($deletedVariant);

        // The deleted variant matches the context, but wording an admin removed must never be sent.
        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($defaultVariant, $result);
    }

    public function testGetDefaultVariantIgnoresDeletedVariants(): void
    {
        $section = new Entity();

        $deletedDefault = $this->createVariant();
        $deletedDefault->setDeletedDate(new \DateTime('2026-07-02 07:26:19'));
        $section->addVariant($deletedDefault);

        $liveDefault = $this->createVariant();
        $section->addVariant($liveDefault);

        $this->assertSame($liveDefault, $section->getDefaultVariant());
    }

    public function testExplainVariantForContextReportsFallingBackDespiteConditionedVariants(): void
    {
        $section = new Entity();
        $section->addVariant($this->createVariant());

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $section->addVariant($this->createVariant(goodsOrPsv: $gvRefData, isVariation: false));

        // No application, so isVariation is null and the conditioned variant is unreachable.
        $resolution = $section->explainVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => null,
            'selectedChoiceIds' => [],
        ]);

        $this->assertTrue($resolution->wasDefaultFallback);
        $this->assertSame(1, $resolution->conditionedCount);
        $this->assertTrue($resolution->fellBackDespiteConditionedVariants());
        $this->assertSame(['isVariation'], array_values($resolution->rejections)[0]);
    }

    public function testExplainVariantForContextDoesNotReportFallbackWhenThereIsNothingToFallFrom(): void
    {
        $section = new Entity();
        $section->addVariant($this->createVariant());

        $resolution = $section->explainVariantForContext(['selectedChoiceIds' => []]);

        $this->assertTrue($resolution->wasDefaultFallback);
        $this->assertSame(0, $resolution->conditionedCount);
        $this->assertFalse(
            $resolution->fellBackDespiteConditionedVariants(),
            'a section with only a default has not lost anything'
        );
    }

    public function testExplainVariantForContextReportsDuplicateDefaults(): void
    {
        $section = new Entity();
        $section->addVariant($this->createVariant());
        $section->addVariant($this->createVariant());

        $resolution = $section->explainVariantForContext(['selectedChoiceIds' => []]);

        $this->assertTrue($resolution->hasDuplicateDefaults());
        $this->assertCount(2, $resolution->liveDefaults);
    }

    public function testExplainVariantForContextSeparatesDeletedVariants(): void
    {
        $section = new Entity();
        $default = $this->createVariant();
        $section->addVariant($default);

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $deleted = $this->createVariant(goodsOrPsv: $gvRefData);
        $deleted->setDeletedDate(new \DateTime('2026-07-02'));
        $section->addVariant($deleted);

        $resolution = $section->explainVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($default, $resolution->chosen);
        $this->assertSame([$deleted], $resolution->deleted);
        $this->assertSame(0, $resolution->conditionedCount, 'a deleted variant is not a live option');
    }

    public function testExplainVariantForContextReportsWhenNothingIsUsable(): void
    {
        $section = new Entity();

        $psvRefData = m::mock(RefData::class)->makePartial();
        $psvRefData->setId('lcat_psv');
        $section->addVariant($this->createVariant(goodsOrPsv: $psvRefData));

        $resolution = $section->explainVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
            'selectedChoiceIds' => [],
        ]);

        $this->assertTrue($resolution->hasNoUsableVariant());
        $this->assertFalse($resolution->wasDefaultFallback, 'nothing was chosen, so nothing fell back');
    }

    public function testGetVariantForContextWithDuplicateDefaultsUsesTheFirst(): void
    {
        $section = new Entity();

        // Duplicate defaults exist in live data: the unique key on the condition columns cannot
        // catch them because MySQL treats NULLs as distinct.
        $firstDefault = $this->createVariant();
        $section->addVariant($firstDefault);

        $secondDefault = $this->createVariant();
        $section->addVariant($secondDefault);

        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_psv',
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($firstDefault, $result);
    }

    public function testGetVariantForContextWithChoiceBasedVariant(): void
    {
        $section = new Entity();

        $defaultVariant = $this->createVariant();
        $section->addVariant($defaultVariant);

        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(55);
        $choiceVariant = $this->createVariant(letterChoice: $choice);
        $section->addVariant($choiceVariant);

        // Context includes the choice
        $result = $section->getVariantForContext([
            'selectedChoiceIds' => [55],
        ]);

        $this->assertSame($choiceVariant, $result);

        // Context does not include the choice - falls back to default
        $result = $section->getVariantForContext([
            'selectedChoiceIds' => [99],
        ]);

        $this->assertSame($defaultVariant, $result);
    }

    public function testGetVariantForContextOnlyConditionedVariantsNoDefault(): void
    {
        $section = new Entity();

        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');
        $gvVariant = $this->createVariant(goodsOrPsv: $gvRefData);
        $section->addVariant($gvVariant);

        // Match
        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_gv',
        ]);
        $this->assertSame($gvVariant, $result);

        // No match and no default
        $result = $section->getVariantForContext([
            'goodsOrPsv' => 'lcat_psv',
        ]);
        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant::class, $result);
    }
}
