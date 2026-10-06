<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Record a caseworker's review of a document analysis.
 *
 * Application and licence are the context the caseworker is viewing the analysis in. When
 * supplied, the API checks the analysis belongs to that context, so an analysis id cannot be
 * reviewed through another application's or licence's page.
 *
 * @Transfer\RouteName("backend/document/document_analysis/single/assessment-status")
 * @Transfer\Method("PUT")
 */
final class UpdateDocumentAnalysisAssessmentStatus extends AbstractCommand
{
    use Identity;
    use ApplicationOptional;
    use LicenceOptional;

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

