<?php

declare(strict_types=1);

namespace Olcs\Service\FinancialEvidence;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;

/**
 * Decides the outcome of a financial evidence assessment from its flags, and records it.
 *
 * The flags are the six flagged rows of the analysis' normalised result, as produced by
 * FinancialEvidenceAssessmentTab::flagsFromAnalysis(). The document is approved only when every
 * flag is PASS; any FAIL or SKIPPED (a check that could not be made) rejects it.
 */
class FinancialEvidenceAssessmentService
{
    public function __construct(private readonly CommandSender $commandSender)
    {
    }

    /**
     * @param list<string> $flags FinancialEvidenceAssessmentTab::FLAG_* values
     *
     * @throws \InvalidArgumentException when there are no flags to assess
     */
    public function decide(array $flags): AssessmentStatus
    {
        if ($flags === []) {
            throw new \InvalidArgumentException('An assessment needs at least one flag');
        }

        foreach ($flags as $flag) {
            if ($flag !== FinancialEvidenceAssessmentTab::FLAG_PASS) {
                return AssessmentStatus::REJECTED;
            }
        }

        return AssessmentStatus::APPROVED;
    }

    /**
     * Decide the outcome and record it against the analysis. The context (the application or
     * licence being viewed) goes with the command so the API can refuse an analysis that does
     * not belong to it.
     *
     * @param list<string>            $flags
     * @param 'application'|'licence' $contextKey
     *
     * @return AssessmentStatus|null the outcome recorded, or null if the API did not record it
     */
    public function assess(int $analysisId, array $flags, string $contextKey, int $contextId): ?AssessmentStatus
    {
        $outcome = $this->decide($flags);

        $response = $this->commandSender->send(
            UpdateDocumentAnalysisAssessmentStatus::create([
                'id' => $analysisId,
                'status' => $outcome->value,
                $contextKey => $contextId,
            ])
        );

        return $response->isOk() ? $outcome : null;
    }
}

