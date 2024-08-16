<?php

namespace Dvsa\Olcs\Api\Entity\Submission;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Submission\Submission;

/**
 * SubmissionAction Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="submission_action",
 *    indexes={
 *        @ORM\Index(name="ix_submission_action_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_submission_action_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_submission_action_submission_id", columns={"submission_id"})
 *    }
 * )
 */
class SubmissionAction extends AbstractSubmissionAction
{
    public const ACTION_TYPE_PUBLIC_INQUIRY = 'sub_st_rec_pi';
    public const ACTION_TYPE_TM_PUBLIC_INQUIRY = 'sub_st_rec_tm_pi';
    public const ACTION_TYPE_PROPOSE_TO_REVOKE = 'sub_st_rec_ptr';
    public const ERROR_ACTION_REQUIRES_LEGISLATION = 'This action requires legislation to be specified';

    public function __construct(Submission $submission, $isDecision, array $actionTypes, $comment)
    {
        $this->submission = $submission;
        $this->isDecision = $isDecision;
        $this->actionTypes = $actionTypes;
        $this->comment = $comment;
    }

    public function update(array $actionTypes, $comment)
    {
        $this->actionTypes = $actionTypes;
        $this->comment = $comment;
    }
}
