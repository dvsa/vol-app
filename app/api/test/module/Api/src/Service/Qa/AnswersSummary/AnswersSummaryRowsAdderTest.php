<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswersSummaryRowGenerator;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswersSummaryRowsAdder;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnswersSummaryRowsAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswersSummaryRowsAdderTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGenerate(mixed $isSnapshot): void
    {
        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);

        $supplementedApplicationStep1 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep2 = m::mock(SupplementedApplicationStep::class);

        $supplementedApplicationSteps = [
            $supplementedApplicationStep1,
            $supplementedApplicationStep2
        ];

        $supplementedApplicationStepsProvider = m::mock(SupplementedApplicationStepsProvider::class);
        $supplementedApplicationStepsProvider->shouldReceive('get')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturn($supplementedApplicationSteps);

        $answersSummaryRow1 = m::mock(AnswersSummaryRow::class);
        $answersSummaryRow2 = m::mock(AnswersSummaryRow::class);

        $answersSummaryRowGenerator = m::mock(AnswersSummaryRowGenerator::class);
        $answersSummaryRowGenerator->shouldReceive('generate')
            ->with($supplementedApplicationStep1, $irhpApplicationEntity, $isSnapshot)
            ->once()
            ->andReturn($answersSummaryRow1);
        $answersSummaryRowGenerator->shouldReceive('generate')
            ->with($supplementedApplicationStep2, $irhpApplicationEntity, $isSnapshot)
            ->once()
            ->andReturn($answersSummaryRow2);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->with($answersSummaryRow1)
            ->once()
            ->ordered();
        $answersSummary->shouldReceive('addRow')
            ->with($answersSummaryRow2)
            ->once()
            ->ordered();

        $answersSummaryRowsAdder = new AnswersSummaryRowsAdder(
            $supplementedApplicationStepsProvider,
            $answersSummaryRowGenerator
        );

        $answersSummaryRowsAdder->addRows($answersSummary, $irhpApplicationEntity, $isSnapshot);
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
