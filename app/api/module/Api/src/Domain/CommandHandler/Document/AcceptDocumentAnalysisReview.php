<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocumentAnalysisEntity;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\AcceptDocumentAnalysisReview as Cmd;

/**
 * A caseworker accepts the review of a successful document analysis.
 *
 * The outcome is not in the command. Like Application\Grant, the handler decides it: the
 * analysis' stored normalised result is read, AnalysisReviewOutcome turns its flags into
 * approved or rejected, and the status is recorded as the current user in one guarded UPDATE.
 * The outcome goes back in the result's "assessmentStatus" flag so the caller can report it.
 *
 * An analysis that does not exist is a 404 (fetchUsingId throws NotFoundException). One that
 * exists but cannot be reviewed - not successful, or with nothing to decide on - is a 400
 * (BadRequestException), as GrantBusReg does for a registration that is not grantable: the
 * record is there, the action does not apply to it in its current state.
 *
 * A caseworker choosing a status deliberately is UpdateDocumentAnalysisAssessmentStatus.
 */
final class AcceptDocumentAnalysisReview extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const string FLAG_ASSESSMENT_STATUS = 'assessmentStatus';

    protected $repoServiceName = 'DocumentAnalysis';

    public function __construct(
        private readonly AnalysisResultNormaliser $normaliser,
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

        /** @var DocumentAnalysisRepo $repo */
        $repo = $this->getRepo();

        /** @var DocumentAnalysisEntity $analysis */
        $analysis = $repo->fetchUsingId($command);

        if ($analysis->getStatus() !== DocumentAnalysisEntity::STATUS_SUCCESS) {
            throw new BadRequestException(
                sprintf('Document analysis %d is %s, not SUCCESS, so cannot be reviewed', $analysisId, $analysis->getStatus())
            );
        }

        $normalised = $this->normaliser->fromStored($analysis->getResultNormalised());
        $status = $normalised === null ? null : $this->outcome->decide($normalised);

        if ($status === null) {
            throw new BadRequestException(
                sprintf('Document analysis %d has no assessment to review', $analysisId)
            );
        }

        // Guarded on status = SUCCESS, so a row resolved otherwise since it was read matches nothing.
        if ($repo->recordAssessmentStatus($analysisId, $status, $this->getCurrentUser()) === 0) {
            throw new BadRequestException(
                sprintf('Document analysis %d is no longer a successful analysis, so cannot be reviewed', $analysisId)
            );
        }

        $this->result->addId('documentAnalysis', $analysisId);
        $this->result->setFlag(self::FLAG_ASSESSMENT_STATUS, $status->value);
        $this->result->addMessage(
            sprintf('Document analysis %d review accepted: %s', $analysisId, $status->value)
        );

        return $this->result;
    }
}
