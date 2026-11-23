<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;

/**
 * A caseworker changes one failed or skipped check of a successful analysis to a pass, or leaves
 * a note on it without changing it - and can revisit either answer later ("N" then undoes an
 * earlier change). The reverse of OverridePassedDocumentAnalysisFlag; the rules both share are
 * in AbstractOverrideDocumentAnalysisFlag.
 *
 * @see \Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag
 */
final class OverrideDocumentAnalysisFlag extends AbstractOverrideDocumentAnalysisFlag
{
    #[\Override]
    protected function targetFlag(): string
    {
        return NormalisedResult::FLAG_PASS;
    }

    #[\Override]
    protected function answerableFlags(): array
    {
        return AnalysisAnnotationOverlay::OVERRIDABLE_TO_PASS_FLAGS;
    }

    #[\Override]
    protected function notAnswerableMessage(string $rowKey, int $analysisId): string
    {
        return sprintf('Check %s of document analysis %d is not a fail or skipped, so cannot be changed', $rowKey, $analysisId);
    }
}
