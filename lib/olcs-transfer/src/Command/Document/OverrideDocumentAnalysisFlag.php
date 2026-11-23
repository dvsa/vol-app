<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * A caseworker changes one failed or skipped check of an automated document analysis to a pass,
 * or leaves a note on it without changing it - and can revisit either answer later.
 *
 * Only the row, the choice and the comment travel: the API records who made the change and when,
 * and refuses anything that cannot be changed (an analysis that is not successful, or a row the
 * analyser did not fail or skip). Deciding the review itself is UpdateDocumentAnalysisAssessmentStatus.
 *
 * changeRecommendation is "Y" to change the row to a pass, or "N" to leave it as the analyser
 * said - which, on a row changed earlier, undoes that change. The comment is not required here:
 * "Y" without one is refused by the caller, which always asks for a reason; "N" is a genuine
 * request whether or not one is given.
 */
#[Transfer\RouteName('backend/document/document_analysis/single/override-flag')]
#[Transfer\Method('PUT')]
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
     */
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => self::ROWS, 'strict' => true])]
    protected $row;

    /**
     * @var string
     */
    // Optional: "N" with no comment is a real request (it undoes an earlier change, or leaves a
    // row untouched), and an input that is not marked optional is refused when empty.
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 1000])]
    protected $comment;

    /**
     * @var string
     */
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => ['Y', 'N'], 'strict' => true])]
    protected $changeRecommendation;

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

    /**
     * @return string
     */
    public function getChangeRecommendation()
    {
        return $this->changeRecommendation;
    }
}
