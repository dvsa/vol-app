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
 * and refuses anything that cannot be changed (an analysis that is not successful or is already
 * decided, or a row that is not currently a pass). Deciding the review itself is
 * UpdateDocumentAnalysisAssessmentStatus.
 *
 * changeRecommendation is "Y" to change the row to a fail, or "N" to leave it as it is; either
 * way the comment, if given, is kept as the caseworker's note. The comment is not required here:
 * every row this command can target is a genuine pass, so changing it always needs a reason,
 * which the caller enforces.
 */
#[Transfer\RouteName('backend/document/document_analysis/single/override-pass-flag')]
#[Transfer\Method('PUT')]
final class OverridePassedDocumentAnalysisFlag extends AbstractCommand
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


