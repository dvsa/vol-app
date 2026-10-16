<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Letter\PreviewRecordSuggester;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(PreviewRecordSuggester::class)]
final class PreviewRecordSuggesterTest extends MockeryTestCase
{
    private PreviewRecordSuggester $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new PreviewRecordSuggester(m::mock(EntityManagerInterface::class));
    }

    private function refData(string $id): RefData
    {
        $refData = new RefData();
        $refData->setId($id);
        return $refData;
    }

    private function variant(
        ?string $goodsOrPsv = null,
        ?bool $isVariation = null,
        ?bool $isNi = null,
        ?string $orgType = null,
        bool $deleted = false
    ): m\MockInterface {
        $variant = m::mock(LetterSectionVariant::class);
        $variant->shouldReceive('isDeleted')->andReturn($deleted);
        $variant->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv === null ? null : $this->refData($goodsOrPsv));
        $variant->shouldReceive('getIsVariation')->andReturn($isVariation);
        $variant->shouldReceive('getIsNi')->andReturn($isNi);
        $variant->shouldReceive('getOrganisationType')->andReturn($orgType === null ? null : $this->refData($orgType));
        return $variant;
    }

    private function section(array $variants): m\MockInterface
    {
        $section = m::mock(LetterSection::class);
        $section->shouldReceive('getVariants')->andReturn(new ArrayCollection($variants));
        return $section;
    }

    public function testIdenticalVectorsAcrossSectionsCollapseToOneCombination(): void
    {
        // Intro and closing both branch GV/Variation: one record covers both, so one row
        $sections = [
            $this->section([$this->variant('lcat_gv', true)]),
            $this->section([$this->variant('lcat_gv', true)]),
        ];

        $combinations = $this->sut->deriveCombinations($sections);

        $this->assertCount(1, $combinations);
        $this->assertSame(
            ['goodsOrPsv' => 'lcat_gv', 'isVariation' => true],
            array_values($combinations)[0]
        );
    }

    public function testDeletedAndDefaultVariantsContributeNothing(): void
    {
        $sections = [
            $this->section([
                $this->variant(),                                  // default: any record matches
                $this->variant('lcat_psv', false, deleted: true),  // deleted: never matches
            ]),
        ];

        $this->assertSame([], $this->sut->deriveCombinations($sections));
    }

    public function testDistinctVectorsEachGetARow(): void
    {
        $sections = [
            $this->section([
                $this->variant('lcat_gv', false),
                $this->variant('lcat_psv', true, false, 'org_t_st'),
            ]),
        ];

        $combinations = array_values($this->sut->deriveCombinations($sections));

        $this->assertCount(2, $combinations);
        $this->assertContains(['goodsOrPsv' => 'lcat_gv', 'isVariation' => false], $combinations);
        $this->assertContains(
            ['goodsOrPsv' => 'lcat_psv', 'isVariation' => true, 'isNi' => false, 'organisationType' => 'org_t_st'],
            $combinations
        );
    }

    public function testSuggestPairsEveryCombinationWithitsExampleOrNull(): void
    {
        /** @var PreviewRecordSuggester|m\MockInterface $sut */
        $sut = m::mock(PreviewRecordSuggester::class, [m::mock(EntityManagerInterface::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $record = ['applicationId' => 3, 'licenceId' => 7, 'licNo' => 'OB1', 'status' => 'Valid', 'isVariation' => false];
        $sut->shouldReceive('findExampleFor')
            ->with(['goodsOrPsv' => 'lcat_gv', 'isVariation' => false])->andReturn($record);
        $sut->shouldReceive('findExampleFor')
            ->with(['goodsOrPsv' => 'lcat_psv', 'isVariation' => true])->andReturn(null);

        $suggestions = $sut->suggest([
            $this->section([
                $this->variant('lcat_gv', false),
                $this->variant('lcat_psv', true),
            ]),
        ]);

        $this->assertCount(2, $suggestions);
        $byFound = array_column($suggestions, 'record');
        $this->assertContains($record, $byFound);
        $this->assertContains(null, $byFound, '"none found" is a result, not an omission');
    }
}
