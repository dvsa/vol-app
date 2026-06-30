<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * LetterSectionVariant Entity Unit Tests
 */
class LetterSectionVariantEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Helper to create a variant with specific conditions
     */
    private function createVariant(
        ?RefData $goodsOrPsv = null,
        ?bool $isVariation = null,
        ?bool $isNi = null,
        ?LetterChoice $letterChoice = null
    ): Entity {
        $variant = new Entity();
        $variant->setGoodsOrPsv($goodsOrPsv);
        $variant->setIsVariation($isVariation);
        $variant->setIsNi($isNi);
        $variant->setLetterChoice($letterChoice);

        return $variant;
    }

    // ---------------------------------------------------------------
    // matchesContext() tests
    // ---------------------------------------------------------------

    public function testMatchesContextDefaultVariantMatchesAnyContext(): void
    {
        $variant = $this->createVariant();

        // Default variant (all NULL) should match any context
        $this->assertTrue($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'isNi' => false,
            'selectedChoiceIds' => [10, 20],
        ]));

        // Also matches empty context
        $this->assertTrue($variant->matchesContext([]));
    }

    public function testMatchesContextGoodsOrPsvMatchesGvContext(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $variant = $this->createVariant(goodsOrPsv: $gvRefData);

        $this->assertTrue($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
        ]));
    }

    public function testMatchesContextGoodsOrPsvDoesNotMatchPsvContext(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $variant = $this->createVariant(goodsOrPsv: $gvRefData);

        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_psv',
        ]));
    }

    public function testMatchesContextIsVariationTrueMatchesVariationContext(): void
    {
        $variant = $this->createVariant(isVariation: true);

        $this->assertTrue($variant->matchesContext([
            'isVariation' => true,
        ]));
    }

    public function testMatchesContextIsVariationTrueDoesNotMatchNewApplicationContext(): void
    {
        $variant = $this->createVariant(isVariation: true);

        $this->assertFalse($variant->matchesContext([
            'isVariation' => false,
        ]));
    }

    public function testMatchesContextIsNiTrueMatchesNiContext(): void
    {
        $variant = $this->createVariant(isNi: true);

        $this->assertTrue($variant->matchesContext([
            'isNi' => true,
        ]));
    }

    public function testMatchesContextIsNiTrueDoesNotMatchGbContext(): void
    {
        $variant = $this->createVariant(isNi: true);

        $this->assertFalse($variant->matchesContext([
            'isNi' => false,
        ]));
    }

    public function testMatchesContextLetterChoiceMatchesWhenChoiceInSelectedIds(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(42);

        $variant = $this->createVariant(letterChoice: $choice);

        $this->assertTrue($variant->matchesContext([
            'selectedChoiceIds' => [10, 42, 99],
        ]));
    }

    public function testMatchesContextLetterChoiceDoesNotMatchWhenChoiceAbsent(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(42);

        $variant = $this->createVariant(letterChoice: $choice);

        $this->assertFalse($variant->matchesContext([
            'selectedChoiceIds' => [10, 99],
        ]));
    }

    public function testMatchesContextLetterChoiceDoesNotMatchWhenSelectedChoiceIdsEmpty(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(42);

        $variant = $this->createVariant(letterChoice: $choice);

        $this->assertFalse($variant->matchesContext([
            'selectedChoiceIds' => [],
        ]));
    }

    public function testMatchesContextMultipleConditionsAllMustMatch(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(42);

        $variant = $this->createVariant(
            goodsOrPsv: $gvRefData,
            isVariation: true,
            isNi: false,
            letterChoice: $choice
        );

        // All conditions match
        $this->assertTrue($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'isNi' => false,
            'selectedChoiceIds' => [42],
        ]));

        // One condition fails (wrong goodsOrPsv)
        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_psv',
            'isVariation' => true,
            'isNi' => false,
            'selectedChoiceIds' => [42],
        ]));

        // One condition fails (wrong isVariation)
        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => false,
            'isNi' => false,
            'selectedChoiceIds' => [42],
        ]));

        // One condition fails (wrong isNi)
        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'isNi' => true,
            'selectedChoiceIds' => [42],
        ]));

        // One condition fails (choice missing)
        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'isNi' => false,
            'selectedChoiceIds' => [99],
        ]));
    }

    public function testMatchesContextPartialContextMissingKeysDoNotMatchNonNullConditions(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $variant = $this->createVariant(goodsOrPsv: $gvRefData, isVariation: true);

        // Missing 'goodsOrPsv' key - condition is non-null, so null !== 'lcat_gv'
        $this->assertFalse($variant->matchesContext([
            'isVariation' => true,
        ]));

        // Missing 'isVariation' key - condition is non-null, so null !== true
        $this->assertFalse($variant->matchesContext([
            'goodsOrPsv' => 'lcat_gv',
        ]));

        // Completely empty context
        $this->assertFalse($variant->matchesContext([]));
    }

    public function testMatchesContextGoodsOrPsvWithMissingContextKeyReturnsNull(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $variant = $this->createVariant(goodsOrPsv: $gvRefData);

        // Context has no 'goodsOrPsv' key, so ?? null applies, which is not 'lcat_gv'
        $this->assertFalse($variant->matchesContext([]));
    }

    public function testMatchesContextLetterChoiceWithMissingSelectedChoiceIds(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(42);

        $variant = $this->createVariant(letterChoice: $choice);

        // No selectedChoiceIds in context at all - defaults to empty array
        $this->assertFalse($variant->matchesContext([]));
    }

    // ---------------------------------------------------------------
    // isDefault() tests
    // ---------------------------------------------------------------

    public function testIsDefaultReturnsTrueWhenAllConditionsNull(): void
    {
        $variant = $this->createVariant();

        $this->assertTrue($variant->isDefault());
    }

    public function testIsDefaultReturnsFalseWhenGoodsOrPsvIsSet(): void
    {
        $gvRefData = m::mock(RefData::class)->makePartial();
        $gvRefData->setId('lcat_gv');

        $variant = $this->createVariant(goodsOrPsv: $gvRefData);

        $this->assertFalse($variant->isDefault());
    }

    public function testIsDefaultReturnsFalseWhenIsVariationIsSet(): void
    {
        $variant = $this->createVariant(isVariation: false);

        $this->assertFalse($variant->isDefault());
    }

    public function testIsDefaultReturnsFalseWhenIsNiIsSet(): void
    {
        $variant = $this->createVariant(isNi: true);

        $this->assertFalse($variant->isDefault());
    }

    public function testIsDefaultReturnsFalseWhenLetterChoiceIsSet(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(1);

        $variant = $this->createVariant(letterChoice: $choice);

        $this->assertFalse($variant->isDefault());
    }
}
