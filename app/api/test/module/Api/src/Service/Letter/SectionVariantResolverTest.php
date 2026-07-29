<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionCandidate;
use Dvsa\Olcs\Api\Service\Letter\Resolution\UnresolvedSection;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * SectionVariantResolver
 *
 * This class decides what wording a letter carries. The Letter Type Builder preview and real
 * letter generation both go through it precisely so the preview cannot drift from reality, so
 * the cases below are about the resolution rules themselves rather than either caller.
 */
class SectionVariantResolverTest extends TestCase
{
    private SectionVariantResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new SectionVariantResolver();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    private function refData(string $id): RefData
    {
        $refData = m::mock(RefData::class)->makePartial();
        $refData->setId($id);

        return $refData;
    }

    private function version(): LetterSectionVersion
    {
        return new LetterSectionVersion();
    }

    private function variant(
        ?RefData $goodsOrPsv = null,
        ?bool $isVariation = null,
        ?bool $isNi = null,
        ?LetterChoice $letterChoice = null,
        bool $withVersion = true,
        ?\DateTime $deletedDate = null
    ): LetterSectionVariant {
        $variant = new LetterSectionVariant();
        $variant->setGoodsOrPsv($goodsOrPsv);
        $variant->setIsVariation($isVariation);
        $variant->setIsNi($isNi);
        $variant->setLetterChoice($letterChoice);
        $variant->setDeletedDate($deletedDate);

        if ($withVersion) {
            $version = $this->version();
            $variant->addVersion($version);
            $variant->setCurrentVersion($version);
        }

        return $variant;
    }

    /**
     * @param LetterSectionVariant[] $variants
     */
    private function section(array $variants, string $key = 'a-section'): LetterSection
    {
        $section = new LetterSection();
        $section->setSectionKey($key);

        foreach ($variants as $variant) {
            $section->addVariant($variant);
        }

        return $section;
    }

    private function candidate(LetterSection $section, int $displayOrder = 0, bool $isRequired = false): SectionCandidate
    {
        return new SectionCandidate($section, $displayOrder, $isRequired);
    }

    public function testResolvesTheDefaultVariantWhenNothingElseMatches(): void
    {
        $default = $this->variant();
        $section = $this->section([$default]);

        $result = $this->sut->resolve([$this->candidate($section)], ['selectedChoiceIds' => []]);

        $this->assertCount(1, $result->resolved);
        $this->assertCount(0, $result->unresolved);
        $this->assertSame($default, $result->resolved[0]->variant);
    }

    public function testPrefersTheNarrowestMatchingVariant(): void
    {
        $gv = $this->refData('lcat_gv');

        $default = $this->variant();
        $broad = $this->variant(goodsOrPsv: $gv);
        $narrow = $this->variant(goodsOrPsv: $gv, isVariation: true);

        $section = $this->section([$default, $broad, $narrow]);

        $result = $this->sut->resolve([$this->candidate($section)], [
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => true,
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($narrow, $result->resolved[0]->variant);
    }

    public function testIgnoresDeletedVariants(): void
    {
        $gv = $this->refData('lcat_gv');

        $default = $this->variant();
        $deleted = $this->variant(goodsOrPsv: $gv, deletedDate: new \DateTime('2026-07-02'));

        $section = $this->section([$default, $deleted]);

        $result = $this->sut->resolve([$this->candidate($section)], [
            'goodsOrPsv' => 'lcat_gv',
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($default, $result->resolved[0]->variant, 'wording an admin deleted must never be sent');
    }

    /**
     * Letters generated from a licence have no application, so isVariation is null. A variant that
     * pins isVariation cannot match, and the letter silently falls back to the default -- the
     * behaviour the builder's diagnostics exist to make visible.
     */
    public function testAVariantPinningIsVariationCannotMatchANullContext(): void
    {
        $gv = $this->refData('lcat_gv');

        $default = $this->variant();
        $conditioned = $this->variant(goodsOrPsv: $gv, isVariation: false);

        $section = $this->section([$default, $conditioned]);

        $result = $this->sut->resolve([$this->candidate($section)], [
            'goodsOrPsv' => 'lcat_gv',
            'isVariation' => null,
            'selectedChoiceIds' => [],
        ]);

        $this->assertSame($default, $result->resolved[0]->variant);
    }

    public function testMatchesOnSelectedChoice(): void
    {
        $choice = m::mock(LetterChoice::class)->makePartial();
        $choice->setId(2);

        $default = $this->variant();
        $forChoice = $this->variant(letterChoice: $choice);

        $section = $this->section([$default, $forChoice]);

        $matched = $this->sut->resolve([$this->candidate($section)], ['selectedChoiceIds' => [2]]);
        $this->assertSame($forChoice, $matched->resolved[0]->variant);

        $notMatched = $this->sut->resolve([$this->candidate($section)], ['selectedChoiceIds' => [99]]);
        $this->assertSame($default, $notMatched->resolved[0]->variant);
    }

    public function testReportsSectionsWithNoMatchingVariant(): void
    {
        $psv = $this->refData('lcat_psv');
        $section = $this->section([$this->variant(goodsOrPsv: $psv)], 'psv-only');

        $result = $this->sut->resolve(
            [$this->candidate($section, isRequired: true)],
            ['goodsOrPsv' => 'lcat_gv', 'selectedChoiceIds' => []]
        );

        $this->assertCount(0, $result->resolved);
        $this->assertTrue($result->hasUnresolved());
        $this->assertSame(UnresolvedSection::REASON_NO_MATCHING_VARIANT, $result->unresolved[0]->reason);
        $this->assertSame('psv-only', $result->unresolved[0]->getSectionName());
    }

    /**
     * A variant that matches but carries no current version is a different failure from one that
     * never matched, and an admin needs to be told which -- the first is a context problem, the
     * second is unpublished content.
     */
    public function testDistinguishesAMatchedVariantWithNoCurrentVersion(): void
    {
        $section = $this->section([$this->variant(withVersion: false)]);

        $result = $this->sut->resolve([$this->candidate($section)], ['selectedChoiceIds' => []]);

        $this->assertCount(0, $result->resolved);
        $this->assertSame(UnresolvedSection::REASON_NO_CURRENT_VERSION, $result->unresolved[0]->reason);
    }

    public function testSplitsUnresolvedSectionsByWhetherTheLetterTypeRequiresThem(): void
    {
        $psv = $this->refData('lcat_psv');
        $required = $this->section([$this->variant(goodsOrPsv: $psv)], 'required-section');
        $optional = $this->section([$this->variant(goodsOrPsv: $psv)], 'optional-section');

        $result = $this->sut->resolve(
            [
                $this->candidate($required, isRequired: true),
                $this->candidate($optional, isRequired: false),
            ],
            ['goodsOrPsv' => 'lcat_gv', 'selectedChoiceIds' => []]
        );

        $this->assertCount(1, $result->getUnresolvedRequired());
        $this->assertSame('required-section', $result->getUnresolvedRequired()[0]->getSectionName());

        $this->assertCount(1, $result->getUnresolvedOptional());
        $this->assertSame('optional-section', $result->getUnresolvedOptional()[0]->getSectionName());
    }

    /**
     * The builder expresses drag-to-reorder as the order of the candidate list, and the Update
     * command derives display_order from array position on save. Resolution must therefore
     * preserve the order it was given rather than imposing one of its own.
     */
    public function testPreservesCompositionOrder(): void
    {
        $first = $this->section([$this->variant()], 'first');
        $second = $this->section([$this->variant()], 'second');
        $third = $this->section([$this->variant()], 'third');

        $result = $this->sut->resolve(
            [
                $this->candidate($first, displayOrder: 0),
                $this->candidate($second, displayOrder: 1),
                $this->candidate($third, displayOrder: 2),
            ],
            ['selectedChoiceIds' => []]
        );

        $keys = array_map(static fn($r): string => $r->section->getSectionKey(), $result->resolved);
        $this->assertSame(['first', 'second', 'third'], $keys);
        $this->assertSame([0, 1, 2], array_map(static fn($r): int => $r->displayOrder, $result->resolved));
    }

    public function testResolvesNothingForAnEmptyComposition(): void
    {
        $result = $this->sut->resolve([], ['selectedChoiceIds' => []]);

        $this->assertSame([], $result->resolved);
        $this->assertFalse($result->hasUnresolved());
    }

    /**
     * The saved composition is the fallback the builder previews before an admin changes anything,
     * so it has to carry display order and the required flag across from the join rows.
     */
    public function testBuildsCandidatesFromASavedLetterType(): void
    {
        $first = $this->section([$this->variant()], 'first');
        $second = $this->section([$this->variant()], 'second');

        $letterType = m::mock(LetterType::class)->makePartial();
        $letterType->shouldReceive('getLetterTypeSections')->andReturn(new ArrayCollection([
            $this->typeSection($first, 0, true),
            $this->typeSection($second, 1, false),
        ]));

        $candidates = SectionCandidate::listFromLetterType($letterType);

        $this->assertCount(2, $candidates);
        $this->assertSame($first, $candidates[0]->section);
        $this->assertSame(0, $candidates[0]->displayOrder);
        $this->assertTrue($candidates[0]->isRequired);
        $this->assertFalse($candidates[1]->isRequired);
    }

    public function testALetterTypeWithNoSectionsYieldsNoCandidates(): void
    {
        $letterType = m::mock(LetterType::class)->makePartial();
        $letterType->shouldReceive('getLetterTypeSections')->andReturn(new ArrayCollection([]));

        $this->assertSame([], SectionCandidate::listFromLetterType($letterType));
    }

    private function typeSection(LetterSection $section, int $displayOrder, bool $isRequired): LetterTypeSection
    {
        $typeSection = m::mock(LetterTypeSection::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn($displayOrder);
        $typeSection->shouldReceive('getIsRequired')->andReturn($isRequired);

        return $typeSection;
    }
}
