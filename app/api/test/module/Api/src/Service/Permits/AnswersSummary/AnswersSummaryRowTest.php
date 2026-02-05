<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnswersSummaryRowTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswersSummaryRowTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $question = 'question.translation.key';
        $formattedAnswer = '47';
        $slug = 'no-of-permits';

        $expectedRepresentation = [
            'question' => $question,
            'answer' => $formattedAnswer,
            'slug' => $slug
        ];

        $answersSummaryRow = new AnswersSummaryRow($question, $formattedAnswer, $slug);

        $this->assertEquals(
            $expectedRepresentation,
            $answersSummaryRow->getRepresentation()
        );
    }
}
