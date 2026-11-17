<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnalysisReviewOutcomeTest extends TestCase
{
    #[DataProvider('outcomeProvider')]
    public function testDecidesFromTheFlaggedRowsOnly(array $flags, ?AssessmentStatus $expected): void
    {
        $rows = [
            // Information rows carry no flag and must not count either way.
            'bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []],
            'bankAddress' => ['flag' => null, 'remark' => null, 'value' => null, 'checks' => []],
        ];

        foreach ($flags as $key => $flag) {
            $rows[$key] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        $this->assertSame($expected, (new AnalysisReviewOutcome())->decide(NormalisedResult::fromRows($rows)));
    }

    public static function outcomeProvider(): array
    {
        $allPass = [
            'authenticity' => 'pass',
            'name' => 'pass',
            'statementDate' => 'pass',
            'statementPeriod' => 'pass',
            'averageFunds' => 'pass',
            'largeDeposit' => 'pass',
        ];

        return [
            'every flagged row passes' => [$allPass, AssessmentStatus::APPROVED],
            'one row fails' => [['largeDeposit' => 'fail'] + $allPass, AssessmentStatus::REJECTED],
            // A check that could not be made is not a pass.
            'one row skipped' => [['name' => 'skipped'] + $allPass, AssessmentStatus::REJECTED],
            'a flag this code does not know' => [['name' => 'maybe'] + $allPass, AssessmentStatus::REJECTED],
            'no flagged rows at all' => [[], null],
        ];
    }
}
