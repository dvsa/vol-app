<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus;

/**
 * Can Update a Document Analysis with an ID
 *
 * Only internal users review analyses. When the caller supplies the application or licence it
 * is viewing, the analysis must belong to it, so an id cannot be posted through another
 * application's or licence's page.
 */
class CanUpdateDocumentAnalysisWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @param UpdateDocumentAnalysisAssessmentStatus $dto
     */
    #[\Override]
    public function isValid($dto)
    {
        if (!$this->isInternalUser()) {
            return false;
        }

        $id = $dto->getId();

        $licence = $dto->getLicence();
        if ($licence !== null && !$this->documentAnalysisBelongsToLicence($id, $licence)) {
            return false;
        }

        $application = $dto->getApplication();
        if ($application !== null && !$this->documentAnalysisBelongsToApplication($id, $application)) {
            return false;
        }

        return true;
    }
}

