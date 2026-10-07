<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * A caseworker accepts the review of a document analysis.
 *
 * The command carries only the analysis: the outcome (approved or rejected) is decided by the
 * API from the analysis' own normalised result, not chosen by the caller. Compare Application\Grant,
 * where the API decides whether the application can be granted. A caseworker choosing a status
 * deliberately is UpdateDocumentAnalysisAssessmentStatus.
 *
 * @Transfer\RouteName("backend/document/document_analysis/single/accept-review")
 * @Transfer\Method("PUT")
 */
final class AcceptDocumentAnalysisReview extends AbstractCommand
{
    use Identity;
}
