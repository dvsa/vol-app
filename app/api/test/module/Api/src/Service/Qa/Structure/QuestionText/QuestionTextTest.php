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
class QuestionTextTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(
        mixed $questionFilteredTranslateableText,
        mixed $questionSummaryTranslateableText,
        mixed $detailsFilteredTranslateableText,
        mixed $guidanceFilteredTranslateableText,
        mixed $additionalGuidanceFilteredTranslateableText,
        mixed $expectedRepresentation
    ): void {
        $questionText = new QuestionText(
            $questionFilteredTranslateableText,
            $questionSummaryTranslateableText,
            $detailsFilteredTranslateableText,
            $guidanceFilteredTranslateableText,
            $additionalGuidanceFilteredTranslateableText
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

        $questionFilteredTranslateableText = m::mock(FilteredTranslateableText::class);
        $questionFilteredTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($questionRepresentation);

        $questionSummaryFilteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $detailsFilteredTranslateableText = m::mock(FilteredTranslateableText::class);
        $detailsFilteredTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($detailsRepresentation);

        $guidanceFilteredTranslateableText = m::mock(FilteredTranslateableText::class);
        $guidanceFilteredTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($guidanceRepresentation);

        $additionalGuidanceFilteredTranslateableText = m::mock(FilteredTranslateableText::class);
        $additionalGuidanceFilteredTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($additionalGuidanceRepresentation);

        return [
            'All values present' => [
                $questionFilteredTranslateableText,
                $questionSummaryFilteredTranslateableText,
                $detailsFilteredTranslateableText,
                $guidanceFilteredTranslateableText,
                $additionalGuidanceFilteredTranslateableText,
                [
                    'question' => $questionRepresentation,
                    'details' => $detailsRepresentation,
                    'guidance' => $guidanceRepresentation,
                    'additionalGuidance' => $additionalGuidanceRepresentation
                ]
            ],
            'Some values missing 1' => [
                $questionFilteredTranslateableText,
                $questionSummaryFilteredTranslateableText,
                $detailsFilteredTranslateableText,
                null,
                null,
                [
                    'question' => $questionRepresentation,
                    'details' => $detailsRepresentation,
                ]
            ],
            'Some values missing 2' => [
                null,
                null,
                null,
                $guidanceFilteredTranslateableText,
                $additionalGuidanceFilteredTranslateableText,
                [
                    'guidance' => $guidanceRepresentation,
                    'additionalGuidance' => $additionalGuidanceRepresentation
                ]
            ],
        ];
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
