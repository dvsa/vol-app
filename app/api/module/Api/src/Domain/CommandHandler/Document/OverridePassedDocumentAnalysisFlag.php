<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;

/**
 * A caseworker changes one passed check of a successful analysis to a fail, or leaves a note on
 * it without changing it - and can revisit either answer later ("N" then changes it back to a
 * pass). The reverse of OverrideDocumentAnalysisFlag; the rules both share are in
 * AbstractOverrideDocumentAnalysisFlag.
 *
 * @see \Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag
 */
final class OverridePassedDocumentAnalysisFlag extends AbstractOverrideDocumentAnalysisFlag
{
    #[\Override]
    protected function targetFlag(): string
    {
        return NormalisedResult::FLAG_FAIL;
    }

    #[\Override]
    protected function answerableFlags(): array
    {
        return AnalysisAnnotationOverlay::OVERRIDABLE_TO_FAIL_FLAGS;
    }

    #[\Override]
    protected function notAnswerableMessage(string $rowKey, int $analysisId): string
    {
        return sprintf(
            'Check %s of document analysis %d was not assessed as a pass, so cannot be changed to a fail',
            $rowKey,
            $analysisId
        );
    }
}
