<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(UpdateDocumentAnalysisAssessmentStatus::class)]
final class UpdateDocumentAnalysisAssessmentStatusTest extends TestCase
{
    use CommandTest;

    #[\Override]
    protected function createBlankDto()
    {
        return new UpdateDocumentAnalysisAssessmentStatus();
    }

    #[\Override]
    protected function getOptionalDtoFields()
    {
        return ['application', 'licence'];
    }

    #[\Override]
    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
            'application' => ['1', '2'],
            'licence' => ['1', '2'],
            'status' => ['PENDING', 'APPROVED', 'REJECTED'],
        ];
    }

    #[\Override]
    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', 'abc', null],
            // Strict, so only the exact enum values pass.
            'status' => ['approved', 'SUCCESS', '', null, 1],
        ];
    }

    #[\Override]
    protected function getFilterTransformations()
    {
        return [];
    }
}

