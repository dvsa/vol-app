<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\AnnualTestHistory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AnnualTestHistory::class)]
class AnnualTestHistoryTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = AnnualTestHistory::class;

    public function sectionTestProvider(): array
    {
        $case = $this->getCase();

        $expectedResult = ['data' => ['text' => 'ath']];

        return [
            [$case, $expectedResult],
        ];
    }
}
