<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocumentAnalysisEntity;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus as Cmd;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;

/**
 * Records a caseworker's review (approved, rejected or back to pending) of a successful
 * document analysis: the "change document review" action on the automated analyser's verdict.
 *
 * Approval is only accepted when every check reads as a pass once the caseworker's changes
 * (annotations) are laid over the analyser's result: each failed or skipped check must be
 * changed to a pass before the evidence can be approved. Otherwise a ValidationException
 * keyed ERR_UNCHANGED_ISSUES is thrown so the caller can explain it. Rejection needs no such check.
 *
 * Ownership of the analysis by the application or licence being viewed is checked by the
 * validation handler before this runs; this only performs the guarded write.
 */
final class UpdateDocumentAnalysisAssessmentStatus extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const string ERR_UNCHANGED_ISSUES = 'ERR_DOCUMENT_ANALYSIS_UNCHANGED_ISSUES';

    /** Every flagged check must read as a pass: fails and skips both block approval. */
    public const string MSG_UNCHANGED_ISSUES =
        'Change all failed and skipped checks to a pass before you accept the financial evidence';

    protected $repoServiceName = 'DocumentAnalysis';

    public function __construct(
        private readonly AnalysisResultNormaliser $normaliser,
        private readonly AnalysisAnnotationOverlay $overlay,
        private readonly AnalysisReviewOutcome $outcome,
    ) {
    }

    /**
     * @param Cmd $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $analysisId = (int)$command->getId();
        // The transfer validation has already restricted the value; from() still refuses anything else.
        $status = AssessmentStatus::from((string)$command->getStatus());

        /** @var DocumentAnalysisRepo $repo */
        $repo = $this->getRepo();

        if ($status === AssessmentStatus::APPROVED) {
            /** @var DocumentAnalysisEntity $analysis */
            $analysis = $repo->fetchUsingId($command);
            $this->guardApproval($analysis);
        }

        if ($repo->recordAssessmentStatus($analysisId, $status, $this->getCurrentUser()) === 0) {
            throw new NotFoundException(
                sprintf('No successful document analysis %d to review', $analysisId)
            );
        }

        $this->result->addId('documentAnalysis', $analysisId);
        $this->result->addMessage(
            sprintf('Document analysis %d assessment status set to %s', $analysisId, $status->value)
        );

        return $this->result;
    }

    /**
     * @throws ValidationException when any check still reads as a fail or skipped
     */
    private function guardApproval(DocumentAnalysisEntity $analysis): void
    {
        $normalised = $this->normaliser->fromStored($analysis->getResultNormalised());

        // No assessment means nothing to check against, so approval rests on the caseworker alone.
        if ($normalised === null) {
            return;
        }

        $decision = $this->outcome->decide($this->overlay->apply($normalised, $analysis->getAnnotations()));

        if ($decision !== null && $decision !== AssessmentStatus::APPROVED) {
            // Worded as what to do next, not what is wrong; the internal app shows it as given.
            throw new ValidationException([
                self::ERR_UNCHANGED_ISSUES => self::MSG_UNCHANGED_ISSUES,
            ]);
        }
    }
}
