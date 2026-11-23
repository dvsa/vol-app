<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * A caseworker changes one passed check of an automated document analysis to a fail, giving the
 * reason in a comment. The reverse of OverrideDocumentAnalysisFlag.
 *
 * Only the row, the comment and the choice travel: the API records who made the change and when,
 * and refuses anything that cannot be changed (an analysis that is not successful, or a row the
 * analyser did not pass). Deciding the review itself is UpdateDocumentAnalysisAssessmentStatus.
 *
 * The answer on a check stands and can be sent again. changeRecommendation "Y" changes the row
 * to a fail, or keeps it one and replaces the comment. "N" leaves it as the analyser had it,
 * which changes a row already failed back to a pass. Either way the comment, if given, is kept
 * against the row. The comment is not required by this command's own
 * validation, because "N" with no comment is a real request. Changing a row to a fail does
 * need a reason, and the API's handler refuses "Y" without one.
 */
#[Transfer\RouteName('backend/document/document_analysis/single/override-pass-flag')]
#[Transfer\Method('PUT')]
final class OverridePassedDocumentAnalysisFlag extends AbstractCommand
{
    use Identity;

    /** The flagged rows of the normalised result; the same rows either direction can change. */
    public const array ROWS = OverrideDocumentAnalysisFlag::ROWS;

    /**
     * @var string
     */
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => self::ROWS, 'strict' => true])]
    protected $row;

    /**
     * @var string
     */
    // Optional: "N" with no comment is a real request (it removes the caseworker's entry for the
    // check), and an input that is not marked optional is refused when empty.
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
