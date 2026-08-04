<?php

namespace Dvsa\Olcs\Transfer\Query\Submission;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class SubmissionSectionComment
 * @Transfer\RouteName("backend/submission-section-comment/single")
 */
class SubmissionSectionComment extends AbstractQuery
{
    use Identity;
}
