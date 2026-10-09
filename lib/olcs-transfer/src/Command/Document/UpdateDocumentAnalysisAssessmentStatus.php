<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * A caseworker sets the review status of a document analysis deliberately (the "change
 * document review" action). Accepting a review, where the API decides the outcome, is
 * AcceptDocumentAnalysisReview.
 *
 * @Transfer\RouteName("backend/document/document_analysis/single/assessment-status")
 * @Transfer\Method("PUT")
 */
final class UpdateDocumentAnalysisAssessmentStatus extends AbstractCommand
{
    use Identity;

    /**
     * One of Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus.
     *
     * @var string
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": \Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus::VALUES,
     *          "strict": true
     *     }
     * )
     */
    protected $status;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
