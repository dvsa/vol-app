<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Service\Letter\CompositionDiagnostics;
use Dvsa\Olcs\Api\Service\Letter\Resolution\ResolvedSection;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;
use Dvsa\Olcs\Api\Service\Letter\Resolution\UnresolvedSection;
use Dvsa\Olcs\Api\Service\Letter\Resolution\VariantResolution;
use Dvsa\Olcs\Api\Entity\System\RefData;
use PHPUnit\Framework\TestCase;

/**
 * CompositionDiagnostics
 *
 * Everything here is drawn from what resolution already worked out, so these tests are about
 * whether an admin is told the right thing rather than about resolution itself.
 */
class CompositionDiagnosticsTest extends TestCase
{
    private CompositionDiagnostics $sut;

    protected function setUp(): void
    {
        $this->sut = new CompositionDiagnostics();
    }

    private function section(string $key): LetterSection
    {
        $section = new LetterSection();
        $section->setSectionKey($key);

        return $section;
    }

    private function variantResolution(
        bool $wasDefaultFallback = false,
        int $conditionedCount = 0,
        int $liveDefaults = 1,
        int $deleted = 0,
        array $rejections = []
    ): VariantResolution {
        // Bare dimension lists become full rejection entries against an unpinned variant, so
        // existing cases keep reading clearly while the structure carries the variant itself
        $entries = [];
        foreach ($rejections as $key => $rejection) {
            $entries[$key] = isset($rejection['variant'])
                ? $rejection
                : ['variant' => new LetterSectionVariant(), 'failed' => $rejection];
        }

        return new VariantResolution(
            new LetterSectionVariant(),
            $wasDefaultFallback,
            $conditionedCount,
            array_fill(0, $liveDefaults, new LetterSectionVariant()),
            array_fill(0, $deleted, new LetterSectionVariant()),
            $entries
        );
    }

    private function resolved(string $key, ?VariantResolution $variantResolution): ResolvedSection
    {
        return new ResolvedSection(
            $this->section($key),
            new LetterSectionVariant(),
            new LetterSectionVersion(),
            0,
            false,
            $variantResolution
        );
    }

    /**
     * @param array<int, array{code:string}> $diagnostics
     */
    private function codes(array $diagnostics): array
    {
        return array_map(static fn(array $d): string => $d['code'], $diagnostics);
    }

    private function firstOfCode(array $diagnostics, string $code): ?array
    {
        foreach ($diagnostics as $diagnostic) {
            if ($diagnostic['code'] === $code) {
                return $diagnostic;
            }
        }

        return null;
    }

    public function testACleanCompositionProducesNoDiagnostics(): void
    {
        $resolution = new SectionResolution([$this->resolved('intro', $this->variantResolution())], []);

        $this->assertSame([], $this->sut->forResolution($resolution, '<p>Nothing to report.</p>'));
    }

    /**
     * Whether a missing section stops the letter is the letter type's call, which is what
     * is_required records -- so the same failure is blocking or advisory depending on it.
     */
    public function testAMissingRequiredSectionBlocksAndAMissingOptionalOneWarns(): void
    {
        $resolution = new SectionResolution([], [
            new UnresolvedSection($this->section('needed'), 0, true, UnresolvedSection::REASON_NO_MATCHING_VARIANT),
            new UnresolvedSection($this->section('nice-to-have'), 1, false, UnresolvedSection::REASON_NO_MATCHING_VARIANT),
        ]);

        $diagnostics = $this->sut->forResolution($resolution);

        $this->assertSame(['sectionUnresolved', 'sectionUnresolved'], $this->codes($diagnostics));
        $this->assertSame(CompositionDiagnostics::SEVERITY_BLOCKING, $diagnostics[0]['severity']);
        $this->assertSame(CompositionDiagnostics::SEVERITY_WARNING, $diagnostics[1]['severity']);
        $this->assertStringContainsString('needed', $diagnostics[0]['message']);
    }

    /**
     * "No variant matches" and "the variant has nothing published" are different problems -- one is
     * the admin's context, the other is unpublished content -- so the message has to separate them.
     */
    public function testUnpublishedContentIsReportedDifferentlyFromAContextMismatch(): void
    {
        $resolution = new SectionResolution([], [
            new UnresolvedSection($this->section('unpublished'), 0, false, UnresolvedSection::REASON_NO_CURRENT_VERSION),
        ]);

        $message = $this->sut->forResolution($resolution)[0]['message'];

        $this->assertStringContainsString('no published version', $message);
        $this->assertStringNotContainsString('no variant matches', $message);
    }

    /**
     * The headline warning: specific wording exists but this context cannot reach any of it, so the
     * catch-all goes out instead. Naming the blocking dimension is what makes it actionable.
     */
    public function testFallingBackToDefaultWordingIsReportedWithTheBlockingDimension(): void
    {
        $resolution = new SectionResolution([
            $this->resolved('interim', $this->variantResolution(
                wasDefaultFallback: true,
                conditionedCount: 2,
                rejections: [1 => ['isVariation'], 2 => ['isVariation', 'goodsOrPsv']]
            )),
        ], []);

        $diagnostic = $this->firstOfCode($this->sut->forResolution($resolution), 'defaultFallback');

        $this->assertNotNull($diagnostic);
        $this->assertSame(CompositionDiagnostics::SEVERITY_WARNING, $diagnostic['severity']);
        $this->assertStringContainsString('2 specific variants are configured', $diagnostic['message']);
        $this->assertSame(['isVariation', 'goodsOrPsv'], $diagnostic['detail']['rejectedOn']);
    }

    /**
     * A section with only a default has not lost anything, so saying it "fell back" would be noise.
     */
    public function testASectionWithOnlyADefaultIsNotReportedAsFallingBack(): void
    {
        $resolution = new SectionResolution([
            $this->resolved('intro', $this->variantResolution(wasDefaultFallback: true, conditionedCount: 0)),
        ], []);

        $this->assertNotContains('defaultFallback', $this->codes($this->sut->forResolution($resolution)));
    }

    public function testDuplicateDefaultsAreReported(): void
    {
        $resolution = new SectionResolution([
            $this->resolved('intro', $this->variantResolution(liveDefaults: 2)),
        ], []);

        $diagnostic = $this->firstOfCode($this->sut->forResolution($resolution), 'duplicateDefaults');

        $this->assertNotNull($diagnostic);
        $this->assertStringContainsString('Only the first is ever used', $diagnostic['message']);
        $this->assertSame(2, $diagnostic['detail']['count']);
    }

    public function testDeletedVariantsAreReportedAsInformationRatherThanAProblem(): void
    {
        $resolution = new SectionResolution([
            $this->resolved('intro', $this->variantResolution(deleted: 3)),
        ], []);

        $diagnostic = $this->firstOfCode($this->sut->forResolution($resolution), 'deletedVariants');

        $this->assertNotNull($diagnostic);
        $this->assertSame(CompositionDiagnostics::SEVERITY_INFO, $diagnostic['severity']);
        $this->assertSame(3, $diagnostic['detail']['count']);
    }

    /**
     * By render time every resolvable bookmark has been substituted, so a placeholder still in
     * braces is one the engine never understood and will print to the operator verbatim.
     */
    public function testPlaceholdersSurvivingIntoTheLetterAreBlocking(): void
    {
        $html = '<p>in the name of {Operator Name}, by {Response Deadline}, for {Operator Name}</p>';

        $diagnostic = $this->firstOfCode(
            $this->sut->forResolution(new SectionResolution([], []), $html),
            'unsupportedPlaceholder'
        );

        $this->assertNotNull($diagnostic);
        $this->assertSame(CompositionDiagnostics::SEVERITY_BLOCKING, $diagnostic['severity']);
        $this->assertSame(['{Operator Name}', '{Response Deadline}'], $diagnostic['detail']['tokens']);
        $this->assertStringContainsString('2 placeholder', $diagnostic['message']);
    }

    public function testResolvedBookmarksDoNotProduceAPlaceholderWarning(): void
    {
        $html = '<p>in the name of John Smith Haulage Ltd., by 11/08/2026</p>';

        $this->assertSame([], $this->sut->forResolution(new SectionResolution([], []), $html));
    }

    /**
     * The handler renders without chrome while composing, and may pass no HTML at all. Diagnostics
     * about the composition itself must still come through.
     */
    public function testCompositionWarningsAreReportedWithoutAnyRenderedHtml(): void
    {
        $resolution = new SectionResolution([], [
            new UnresolvedSection($this->section('needed'), 0, true, UnresolvedSection::REASON_NO_MATCHING_VARIANT),
        ]);

        $this->assertSame(['sectionUnresolved'], $this->codes($this->sut->forResolution($resolution)));
    }

    public function testASectionCanRaiseMoreThanOneWarningAtOnce(): void
    {
        $resolution = new SectionResolution([
            $this->resolved('busy', $this->variantResolution(
                wasDefaultFallback: true,
                conditionedCount: 1,
                liveDefaults: 2,
                deleted: 1,
                rejections: [1 => ['isNi']]
            )),
        ], []);

        $this->assertSame(
            ['defaultFallback', 'duplicateDefaults', 'deletedVariants'],
            $this->codes($this->sut->forResolution($resolution))
        );
    }

    private function pinnedVariant(?string $goodsOrPsv, ?bool $isVariation): LetterSectionVariant
    {
        $variant = new LetterSectionVariant();

        if ($goodsOrPsv !== null) {
            $refData = new RefData();
            $refData->setId($goodsOrPsv);
            $variant->setGoodsOrPsv($refData);
        }

        $variant->setIsVariation($isVariation);

        return $variant;
    }

    /**
     * The near miss is what turns "none match" into an instruction: the closest variant's own
     * pinned dimensions are the context that reaches it, so the UI can apply them in one click.
     */
    public function testDefaultFallbackNamesTheClosestVariantAndSuggestsItsContext(): void
    {
        $close = $this->pinnedVariant('lcat_psv', true);   // one dimension away
        $far = $this->pinnedVariant('lcat_gv', false);     // two dimensions away

        $resolution = new SectionResolution([
            $this->resolved('intro', $this->variantResolution(
                wasDefaultFallback: true,
                conditionedCount: 2,
                rejections: [
                    1 => ['variant' => $far, 'failed' => ['goodsOrPsv', 'isVariation']],
                    2 => ['variant' => $close, 'failed' => ['isVariation']],
                ]
            )),
        ], []);

        $diagnostic = $this->firstOfCode($this->sut->forResolution($resolution), 'defaultFallback');

        $this->assertStringContainsString('differs only on Application type', $diagnostic['message']);
        $this->assertSame(['isVariation'], $diagnostic['detail']['failedDimensions']);
        $this->assertSame(
            ['goodsOrPsv' => 'lcat_psv', 'isVariation' => true],
            $diagnostic['detail']['suggestedContext']
        );
    }

    public function testAnUnresolvedSectionCarriesTheSameSuggestion(): void
    {
        $close = $this->pinnedVariant('lcat_psv', null);

        $resolution = new SectionResolution([], [
            new UnresolvedSection(
                $this->section('psv-only'),
                0,
                true,
                UnresolvedSection::REASON_NO_MATCHING_VARIANT,
                $this->variantResolution(
                    conditionedCount: 1,
                    liveDefaults: 0,
                    rejections: [1 => ['variant' => $close, 'failed' => ['goodsOrPsv']]]
                )
            ),
        ]);

        $diagnostic = $this->firstOfCode($this->sut->forResolution($resolution), 'sectionUnresolved');

        $this->assertStringContainsString('differs only on Vehicle type', $diagnostic['message']);
        $this->assertSame(['goodsOrPsv' => 'lcat_psv'], $diagnostic['detail']['suggestedContext']);
    }
}
