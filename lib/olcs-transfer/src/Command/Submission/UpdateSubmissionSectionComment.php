<?php

/**
 * Update SubmissionSectionComment
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits\OptionalEditorJsComment;

/**
 * @Transfer\RouteName("backend/submission-section-comment/single")
 * @Transfer\Method("PUT")
 */
final class UpdateSubmissionSectionComment extends AbstractCommand
{
    // Identity & Locking
    use FieldType\Traits\Identity;
    use FieldType\Traits\Version;
    use OptionalEditorJsComment;
}
