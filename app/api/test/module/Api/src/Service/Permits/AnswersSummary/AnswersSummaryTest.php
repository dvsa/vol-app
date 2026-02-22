<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnswersSummaryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswersSummaryTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $row1Representation = [
            'question' => 'row 1 question',
            'answer' => 'row 1 answer',
            'slug' => 'row1-slug'
        ];

        $answersSummaryRow1 = m::mock(AnswersSummaryRow::class);
        $answersSummaryRow1->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($row1Representation);

        $row2Representation = [
            'question' => 'row 2 question',
            'answer' => 'row 2 answer',
            'slug' => 'row2-slug'
        ];

        $answersSummaryRow2 = m::mock(AnswersSummaryRow::class);
        $answersSummaryRow2->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($row2Representation);

        $answersSummary = new AnswersSummary();
        $answersSummary->addRow($answersSummaryRow1);
        $answersSummary->addRow($answersSummaryRow2);

        $expectedRepresentation = [
            'rows' => [
                $row1Representation,
                $row2Representation
            ]
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $answersSummary->getRepresentation()
        );
    }
}
