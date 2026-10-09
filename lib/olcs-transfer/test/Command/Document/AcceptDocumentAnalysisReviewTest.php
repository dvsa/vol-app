<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\AcceptDocumentAnalysisReview;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(AcceptDocumentAnalysisReview::class)]
final class AcceptDocumentAnalysisReviewTest extends TestCase
{
    use CommandTest;

    #[\Override]
    protected function createBlankDto()
    {
        return new AcceptDocumentAnalysisReview();
    }

    #[\Override]
    protected function getOptionalDtoFields()
    {
        return [];
    }

    #[\Override]
    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
        ];
    }

    #[\Override]
    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', 'abc', null],
        ];
    }

    #[\Override]
    protected function getFilterTransformations()
    {
        return [];
    }
}
