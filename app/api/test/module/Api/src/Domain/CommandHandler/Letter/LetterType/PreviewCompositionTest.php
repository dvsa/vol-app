<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterType;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType\PreviewComposition as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterAppendix as LetterAppendixRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssue as LetterIssueRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterSection as LetterSectionRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterType as LetterTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as LetterSectionVariantEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion as LetterSectionVersionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as MasterTemplateEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection as LetterTypeSectionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Letter\CompositionDiagnostics;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Api\Service\Letter\MasterTemplateResolver;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\PreviewComposition as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * PreviewComposition
 *
 * The screen this serves exists so an admin can see what a letter type produces before anyone
 * generates a real letter, so the properties that matter are: it writes nothing, it previews what
 * is on screen rather than what is saved, and it resolves exactly as real generation would.
 */
final class PreviewCompositionTest extends AbstractCommandHandlerTestCase
{
    private $mockPreviewService;
    private $mockMasterTemplateResolver;

    public function setUp(): void
    {
        $this->mockPreviewService = m::mock(LetterPreviewService::class);
        $this->mockMasterTemplateResolver = m::mock(MasterTemplateResolver::class);

        // The resolver, composer and diagnostics are pure and are the whole point of the shared
        // path -- mocking them here would let the preview drift from real generation, which is
        // exactly the failure this screen is meant to prevent.
        $this->mockedSmServices = [
            SectionVariantResolver::class => new SectionVariantResolver(),
            LetterInstanceComposer::class => new LetterInstanceComposer(),
            CompositionDiagnostics::class => new CompositionDiagnostics(),
            LetterPreviewService::class => $this->mockPreviewService,
            MasterTemplateResolver::class => $this->mockMasterTemplateResolver,
        ];

        $this->sut = new CommandHandler();
        $this->mockRepo('LetterType', LetterTypeRepo::class);
        $this->mockRepo('LetterSection', LetterSectionRepo::class);
        $this->mockRepo('LetterIssue', LetterIssueRepo::class);
        $this->mockRepo('LetterAppendix', LetterAppendixRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    private function sectionWithDefaultVariant(string $key): LetterSectionEntity
    {
        $section = new LetterSectionEntity();
        $section->setSectionKey($key);

        $version = new LetterSectionVersionEntity();
        $variant = new LetterSectionVariantEntity();
        $variant->addVersion($version);
        $variant->setCurrentVersion($version);
        $section->addVariant($variant);

        return $section;
    }

    /**
     * A section whose variants all pin goodsOrPsv to something the context does not match, so it
     * resolves to nothing.
     */
    private function sectionOnlyMatchingPsv(string $key): LetterSectionEntity
    {
        $section = new LetterSectionEntity();
        $section->setSectionKey($key);

        $psv = m::mock(RefData::class)->makePartial();
        $psv->setId('lcat_psv');

        $version = new LetterSectionVersionEntity();
        $variant = new LetterSectionVariantEntity();
        $variant->setGoodsOrPsv($psv);
        $variant->addVersion($version);
        $variant->setCurrentVersion($version);
        $section->addVariant($variant);

        return $section;
    }

    private function letterType(int $id, array $sections = []): LetterTypeEntity
    {
        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($id);

        $typeSections = [];
        foreach ($sections as $displayOrder => $section) {
            $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
            $typeSection->shouldReceive('getLetterSection')->andReturn($section);
            $typeSection->shouldReceive('getDisplayOrder')->andReturn($displayOrder);
            $typeSection->shouldReceive('getIsRequired')->andReturn(false);
            $typeSections[] = $typeSection;
        }

        $letterType->shouldReceive('getLetterTypeSections')->andReturn(new ArrayCollection($typeSections));

        return $letterType;
    }

    private function expectRender(string $html = '<p>rendered</p>'): void
    {
        $this->mockMasterTemplateResolver->shouldReceive('resolve')->andReturn(null);
        $this->mockPreviewService->shouldReceive('renderPreview')->once()->andReturn($html);
    }

    public function testRendersTheSavedCompositionWhenNoneIsProposed(): void
    {
        $letterType = $this->letterType(7, [$this->sectionWithDefaultVariant('intro')]);

        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(7)->once()->andReturn($letterType);
        $this->expectRender('<p>saved composition</p>');

        $result = $this->sut->handleCommand(Cmd::create(['letterType' => 7]));

        $this->assertSame('<p>saved composition</p>', $result->getFlag('html'));
    }

    /**
     * The screen previews what the admin currently has on screen, which is the entire point --
     * otherwise they would have to save to find out what they built.
     */
    public function testPreviewsTheProposedCompositionRatherThanTheSavedOne(): void
    {
        $saved = $this->sectionWithDefaultVariant('saved-section');
        $proposed = $this->sectionWithDefaultVariant('proposed-section');

        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(7)->once()
            ->andReturn($this->letterType(7, [$saved]));
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(42)->once()->andReturn($proposed);

        $captured = null;
        $this->mockMasterTemplateResolver->shouldReceive('resolve')->andReturn(null);
        $this->mockPreviewService->shouldReceive('renderPreview')->once()
            ->andReturnUsing(function ($letterInstance) use (&$captured) {
                $captured = $letterInstance;
                return '<p>rendered</p>';
            });

        $this->sut->handleCommand(Cmd::create(['letterType' => 7, 'sections' => [42]]));

        $this->assertCount(1, $captured->getLetterInstanceSections());
        $this->assertSame(
            $proposed->getVariants()->first()->getCurrentVersion(),
            $captured->getLetterInstanceSections()->first()->getLetterSectionVersion()
        );
    }

    /**
     * Composition order is the display order, matching how LetterType\Update derives display_order
     * from array position when the admin saves.
     */
    public function testCompositionOrderIsPreserved(): void
    {
        $first = $this->sectionWithDefaultVariant('first');
        $second = $this->sectionWithDefaultVariant('second');

        $this->repoMap['LetterType']->shouldReceive('fetchById')->andReturn($this->letterType(7));
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(1)->andReturn($first);
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(2)->andReturn($second);

        $captured = null;
        $this->mockMasterTemplateResolver->shouldReceive('resolve')->andReturn(null);
        $this->mockPreviewService->shouldReceive('renderPreview')->once()
            ->andReturnUsing(function ($letterInstance) use (&$captured) {
                $captured = $letterInstance;
                return '';
            });

        $this->sut->handleCommand(Cmd::create(['letterType' => 7, 'sections' => [2, 1]]));

        $orders = [];
        foreach ($captured->getLetterInstanceSections() as $instanceSection) {
            $orders[] = $instanceSection->getDisplayOrder();
        }
        $this->assertSame([0, 1], $orders);
    }

    /**
     * An unresolved section has no version to read content from and would fatal the renderer, so it
     * must be reported instead of composed. That is the case the diagnostics panel exists for.
     */
    public function testAnUnresolvableSectionIsReportedRatherThanRendered(): void
    {
        $this->repoMap['LetterType']->shouldReceive('fetchById')->andReturn($this->letterType(7));
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(9)
            ->andReturn($this->sectionOnlyMatchingPsv('psv-only'));

        $captured = null;
        $this->mockMasterTemplateResolver->shouldReceive('resolve')->andReturn(null);
        $this->mockPreviewService->shouldReceive('renderPreview')->once()
            ->andReturnUsing(function ($letterInstance) use (&$captured) {
                $captured = $letterInstance;
                return '';
            });

        $result = $this->sut->handleCommand(Cmd::create([
            'letterType' => 7,
            'sections' => [9],
            'goodsOrPsv' => 'lcat_gv',
        ]));

        $this->assertCount(0, $captured->getLetterInstanceSections(), 'nothing to render from');

        $diagnostics = $result->getFlag('diagnostics');
        $this->assertCount(1, $diagnostics);
        $this->assertSame('sectionUnresolved', $diagnostics[0]['code']);
        $this->assertStringContainsString('psv-only', $diagnostics[0]['message']);
    }

    /**
     * Overrides are why the screen is useful: a licence carries no application, so isVariation is
     * null and every variant pinning it is unreachable. Setting the dimensions directly lets an
     * admin see wording that no available record could reach.
     */
    public function testContextOverridesBeatTheValuesDerivedFromTheLicence(): void
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId('lcat_gv');

        $organisation = m::mock(OrganisationEntity::class)->makePartial();

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(7);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv);
        $licence->shouldReceive('isNi')->andReturn(false);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);

        $this->repoMap['LetterType']->shouldReceive('fetchById')->andReturn($this->letterType(7));
        $this->repoMap['Licence']->shouldReceive('fetchById')->with(7)->once()->andReturn($licence);
        $this->expectRender();

        $result = $this->sut->handleCommand(Cmd::create([
            'letterType' => 7,
            'licence' => 7,
            'goodsOrPsv' => 'lcat_psv',   // override the licence's own GV
            'isVariation' => true,        // unreachable from a licence alone
            'selectedChoiceIds' => [2],
        ]));

        $context = $result->getFlag('context');

        $this->assertSame('lcat_psv', $context['goodsOrPsv']);
        $this->assertTrue($context['isVariation']);
        $this->assertFalse($context['isNi'], 'not overridden, so it still comes from the licence');
        $this->assertSame([2], $context['selectedChoiceIds']);
    }


    /**
     * The letter is always rendered through the master template. Without it the renderer returns
     * bare content with no A4 page, stylesheet or letterhead -- which is not a preview of a letter.
     */
    public function testAlwaysRendersThroughTheMasterTemplate(): void
    {
        $masterTemplate = m::mock(MasterTemplateEntity::class)->makePartial();

        $this->repoMap['LetterType']->shouldReceive('fetchById')->andReturn($this->letterType(7));
        $this->mockMasterTemplateResolver->shouldReceive('resolve')->once()->andReturn($masterTemplate);
        $this->mockPreviewService->shouldReceive('renderPreview')->once()
            ->with(m::any(), $masterTemplate)->andReturn('');

        $this->sut->handleCommand(Cmd::create(['letterType' => 7]));
    }

    /**
     * Nothing may be written. The letter instance is built to be thrown away, and the repository is
     * never asked to save it.
     */
    public function testNothingIsPersisted(): void
    {
        $this->repoMap['LetterType']->shouldReceive('fetchById')->andReturn($this->letterType(7));
        $this->repoMap['LetterType']->shouldReceive('save')->never();
        $this->expectRender();

        $this->sut->handleCommand(Cmd::create(['letterType' => 7]));
    }
}
