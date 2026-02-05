<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\QuestionText;

use Dvsa\Olcs\Api\Entity\Generic\QuestionText as QuestionTextEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\JsonDecodingFilteredTranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * QuestionTextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QuestionTextGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $questionKey = '{"questionKeyJson"}';
        $questionSummaryKey = '{"questionSummaryKeyJson"}';
        $detailsKey = '{"detailsKeyJson"}';
        $guidanceKey = '{"guidanceKeyJson"}';
        $additionalGuidanceKey = '{"additionalGuidanceKeyJson"}';

        $transformedQuestionKey = m::mock(FilteredTranslateableText::class);
        $transformedQuestionSummaryKey = m::mock(FilteredTranslateableText::class);
        $transformedDetailsKey = m::mock(FilteredTranslateableText::class);
        $transformedGuidanceKey = m::mock(FilteredTranslateableText::class);
        $transformedAdditionalGuidanceKey = m::mock(FilteredTranslateableText::class);

        $questionText = m::mock(QuestionText::class);

        $questionTextEntity = m::mock(QuestionTextEntity::class);
        $questionTextEntity->shouldReceive('getQuestionKey')
            ->andReturn($questionKey);
        $questionTextEntity->shouldReceive('getQuestionSummaryKey')
            ->andReturn($questionSummaryKey);
        $questionTextEntity->shouldReceive('getDetailsKey')
            ->andReturn($detailsKey);
        $questionTextEntity->shouldReceive('getGuidanceKey')
            ->andReturn($guidanceKey);
        $questionTextEntity->shouldReceive('getAdditionalGuidanceKey')
            ->andReturn($additionalGuidanceKey);

        $questionTextFactory = m::mock(QuestionTextFactory::class);
        $questionTextFactory->shouldReceive('create')
            ->with(
                $transformedQuestionKey,
                $transformedQuestionSummaryKey,
                $transformedDetailsKey,
                $transformedGuidanceKey,
                $transformedAdditionalGuidanceKey
            )
            ->andReturn($questionText);

        $jsonDecodingFilteredTranslateableTextGenerator = m::mock(
            JsonDecodingFilteredTranslateableTextGenerator::class
        );
        $jsonDecodingFilteredTranslateableTextGenerator->shouldReceive('generate')
            ->with($questionKey)
            ->andReturn($transformedQuestionKey);
        $jsonDecodingFilteredTranslateableTextGenerator->shouldReceive('generate')
            ->with($questionSummaryKey)
            ->andReturn($transformedQuestionSummaryKey);
        $jsonDecodingFilteredTranslateableTextGenerator->shouldReceive('generate')
            ->with($detailsKey)
            ->andReturn($transformedDetailsKey);
        $jsonDecodingFilteredTranslateableTextGenerator->shouldReceive('generate')
            ->with($guidanceKey)
            ->andReturn($transformedGuidanceKey);
        $jsonDecodingFilteredTranslateableTextGenerator->shouldReceive('generate')
            ->with($additionalGuidanceKey)
            ->andReturn($transformedAdditionalGuidanceKey);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity->getQuestion->getActiveQuestionText')
            ->andReturn($questionTextEntity);

        $questionTextGenerator = new QuestionTextGenerator(
            $questionTextFactory,
            $jsonDecodingFilteredTranslateableTextGenerator
        );

        $this->assertSame(
            $questionText,
            $questionTextGenerator->generate($qaContext)
        );
    }
}
