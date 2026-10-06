<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus as Cmd;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;

/**
 * Records a caseworker's review (approved, rejected or back to pending) of a successful
 * document analysis.
 *
 * Ownership of the analysis by the application or licence being viewed is checked by the
 * validation handler before this runs; this only performs the guarded write.
 */
final class UpdateDocumentAnalysisAssessmentStatus extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'DocumentAnalysis';

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
}

