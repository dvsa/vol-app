<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\QuestionText;

use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * QuestionTextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class QuestionTextTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(
        ?array $questionRepresentation,
        bool $hasQuestionSummary,
        ?array $detailsRepresentation,
        ?array $guidanceRepresentation,
        ?array $additionalGuidanceRepresentation,
        array $expectedRepresentation
    ): void {
        $questionText = new QuestionText(
            $this->createFilteredTranslateableText($questionRepresentation),
            $hasQuestionSummary ? m::mock(FilteredTranslateableText::class) : null,
            $this->createFilteredTranslateableText($detailsRepresentation),
            $this->createFilteredTranslateableText($guidanceRepresentation),
            $this->createFilteredTranslateableText($additionalGuidanceRepresentation)
        );

        $this->assertEquals(
            $expectedRepresentation,
            $questionText->getRepresentation()
        );
    }

    public static function dpTestGenerate(): array
    {
        $questionRepresentation = ['questionRepresentation'];
        $detailsRepresentation = ['detailsRepresentation'];
        $guidanceRepresentation = ['guidanceRepresentation'];
        $additionalGuidanceRepresentation = ['additionalGuidanceRepresentation'];

        return [
            'All values present' => [
                $questionRepresentation,
                true,
                $detailsRepresentation,
                $guidanceRepresentation,
                $additionalGuidanceRepresentation,
                [
                    'question' => $questionRepresentation,
                    'details' => $detailsRepresentation,
                    'guidance' => $guidanceRepresentation,
                    'additionalGuidance' => $additionalGuidanceRepresentation
                ]
            ],
            'Some values missing 1' => [
                $questionRepresentation,
                true,
                $detailsRepresentation,
                null,
                null,
                [
                    'question' => $questionRepresentation,
                    'details' => $detailsRepresentation,
                ]
            ],
            'Some values missing 2' => [
                null,
                false,
                null,
                $guidanceRepresentation,
                $additionalGuidanceRepresentation,
                [
                    'guidance' => $guidanceRepresentation,
                    'additionalGuidance' => $additionalGuidanceRepresentation
                ]
            ],
        ];
    }

    private function createFilteredTranslateableText(?array $representation): ?FilteredTranslateableText
    {
        if ($representation === null) {
            return null;
        }

        $filteredTranslateableText = m::mock(FilteredTranslateableText::class);
        $filteredTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($representation);

        return $filteredTranslateableText;
    }

    public function testGetQuestion(): void
    {
        $questionFilteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $questionText = new QuestionText(
            $questionFilteredTranslateableText,
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class)
        );

        $this->assertSame(
            $questionFilteredTranslateableText,
            $questionText->getQuestion()
        );
    }

    public function testGetQuestionSummary(): void
    {
        $questionSummaryFilteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $questionText = new QuestionText(
            m::mock(FilteredTranslateableText::class),
            $questionSummaryFilteredTranslateableText,
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class)
        );

        $this->assertSame(
            $questionSummaryFilteredTranslateableText,
            $questionText->getQuestionSummary()
        );
    }

    public function testGetGuidance(): void
    {
        $guidanceFilteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $questionText = new QuestionText(
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            $guidanceFilteredTranslateableText,
            m::mock(FilteredTranslateableText::class)
        );

        $this->assertSame(
            $guidanceFilteredTranslateableText,
            $questionText->getGuidance()
        );
    }

    public function testGetAdditionalGuidance(): void
    {
        $additionalGuidanceFilteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $questionText = new QuestionText(
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            m::mock(FilteredTranslateableText::class),
            $additionalGuidanceFilteredTranslateableText
        );

        $this->assertSame(
            $additionalGuidanceFilteredTranslateableText,
            $questionText->getAdditionalGuidance()
        );
    }
}
