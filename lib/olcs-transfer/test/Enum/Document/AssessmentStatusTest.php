<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Enum\Document;

use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(AssessmentStatus::class)]
final class AssessmentStatusTest extends TestCase
{
    /** VALUES feeds the command's InArray validation, so it must never drift from the cases. */
    public function testValuesMatchTheCases(): void
    {
        $this->assertSame(
            array_map(static fn(AssessmentStatus $status): string => $status->value, AssessmentStatus::cases()),
            AssessmentStatus::VALUES
        );
    }

    public function testValuesAreTheStoredColumnValues(): void
    {
        $this->assertSame(['PENDING', 'APPROVED', 'REJECTED'], AssessmentStatus::VALUES);
    }
}
