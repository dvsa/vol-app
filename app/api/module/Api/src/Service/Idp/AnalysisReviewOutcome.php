<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;

/**
 * Decides the outcome of accepting a document analysis review from its normalised result.
 *
 * The rule: the document is approved only when every flagged row is a pass. A failed check
 * rejects it, and so does a skipped one, because a check that could not be made is not a pass.
 * Bank and bank address carry no flag and take no part.
 *
 * This lives in the API, not the internal app, so the rule is enforced wherever the command
 * comes from and is decided against the stored result at the moment it is recorded.
 */
class AnalysisReviewOutcome
{
    /**
     * @return AssessmentStatus|null null when the result has no flagged rows, so there is
     *                               nothing to decide on
     */
    public function decide(NormalisedResult $result): ?AssessmentStatus
    {
        $flags = [];

        foreach ($result->rows() as $row) {
            if (($row['flag'] ?? null) !== null) {
                $flags[] = $row['flag'];
            }
        }

        if ($flags === []) {
            return null;
        }

        foreach ($flags as $flag) {
            if ($flag !== NormalisedResult::FLAG_PASS) {
                return AssessmentStatus::REJECTED;
            }
        }

        return AssessmentStatus::APPROVED;
    }
}
