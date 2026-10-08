<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * A caseworker changes one failed or skipped check of an automated document analysis to a pass,
 * giving the reason in a comment.
 *
 * Only the row and the comment travel: the API records who made the change and when, and refuses
 * anything that cannot be changed (an analysis that is not successful or is already decided, or a
 * row that is not currently a fail or skipped). Deciding the review itself is
 * UpdateDocumentAnalysisAssessmentStatus.
 *
 * @Transfer\RouteName("backend/document/document_analysis/single/override-flag")
 * @Transfer\Method("PUT")
 */
final class OverrideDocumentAnalysisFlag extends AbstractCommand
{
    use Identity;

    /** The flagged rows of the normalised result; bank and bank address carry no flag. */
    public const array ROWS = [
        'authenticity',
        'name',
        'statementDate',
        'statementPeriod',
        'averageFunds',
        'largeDeposit',
    ];

    /**
     * @var string
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": \Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag::ROWS,
     *          "strict": true
     *     }
     * )
     */
    protected $row;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 1, "max": 1000})
     */
    protected $comment;

    /**
     * @return string
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}

