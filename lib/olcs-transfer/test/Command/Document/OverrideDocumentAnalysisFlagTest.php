<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(OverrideDocumentAnalysisFlag::class)]
final class OverrideDocumentAnalysisFlagTest extends TestCase
{
    use CommandTest;

    #[\Override]
    protected function createBlankDto()
    {
        return new OverrideDocumentAnalysisFlag();
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
            'row' => OverrideDocumentAnalysisFlag::ROWS,
            'comment' => ['Checked by hand', str_repeat('a', 1000)],
        ];
    }

    #[\Override]
    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', 'abc', null],
            // Bank and bank address carry no flag, so they cannot be overridden.
            'row' => ['bank', 'bankAddress', 'nonsense', '', null],
            'comment' => ['', str_repeat('a', 1001), null],
        ];
    }

    #[\Override]
    protected function getFilterTransformations()
    {
        return [
            'comment' => [
                ['  spaced out  ', 'spaced out'],
            ],
        ];
    }
}

