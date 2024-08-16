<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Appeal Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="appeal",
 *    indexes={
 *        @ORM\Index(name="ix_appeal_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_appeal_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_appeal_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_appeal_reason", columns={"reason"}),
 *        @ORM\Index(name="ix_appeal_outcome", columns={"outcome"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_appeal_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Appeal extends AbstractAppeal
{
    /**
     * Appeal constructor
     *
     * @param string $appealNo appeal number
     */
    public function __construct($appealNo)
    {
        $this->setAppealNo($appealNo);
    }

    /**
     * Update appeal
     *
     * @param RefData      $reason           appeal reason
     * @param string       $appealDate       appeal date
     * @param string|null  $appealNo         appeal number
     * @param string|null  $deadlineDate     deadline date
     * @param string|null  $outlineGround    outline ground
     * @param string|null  $hearingDate      hearing date
     * @param string|null  $decisionDate     decision date
     * @param string|null  $papersDueDate    date papers due
     * @param string|null  $papersDueTcDate  date papers due at TC
     * @param string|null  $papersSentDate   date papers sent
     * @param string|null  $papersSentTcDate date papers sent to TC
     * @param string|null  $comment          comment
     * @param RefData|null $outcome          appeal outcome
     * @param string       $isWithdrawn      whether appeal is withdrawn Y/N
     * @param string|null  $withdrawnDate    withdrawn date
     * @param string       $dvsaNotified     whether DVSA notified Y/N
     *
     * @return Appeal
     */
    public function update(
        RefData $reason,
        $appealDate,
        $appealNo,
        $deadlineDate,
        $outlineGround,
        $hearingDate,
        $decisionDate,
        $papersDueDate,
        $papersDueTcDate,
        $papersSentDate,
        $papersSentTcDate,
        $comment,
        $outcome,
        $isWithdrawn,
        $withdrawnDate,
        $dvsaNotified
    ) {
        $this->setReason($reason);
        $this->setAppealDate($this->processDate($appealDate));
        $this->setAppealNo($appealNo);
        $this->setDeadlineDate($this->processDate($deadlineDate));
        $this->setOutlineGround($outlineGround);
        $this->setHearingDate($this->processDate($hearingDate));
        $this->setDecisionDate($this->processDate($decisionDate));
        $this->setPapersDueDate($this->processDate($papersDueDate));
        $this->setPapersDueTcDate($this->processDate($papersDueTcDate));
        $this->setPapersSentDate($this->processDate($papersSentDate));
        $this->setPapersSentTcDate($this->processDate($papersSentTcDate));
        $this->setComment($comment);
        $this->setOutcome($outcome);
        $this->setDvsaNotified($dvsaNotified);

        if ($isWithdrawn === 'Y') {
            $this->setWithdrawnDate($this->processDate($withdrawnDate));
        } else {
            $this->setWithdrawnDate(null);
        }

        return $this;
    }

    /**
     * Has the appeal been completed? Dealt with if outcome or decision date set. Or if its been withdrawn.
     *
     * @return bool
     */
    public function isOutstanding()
    {
        // Appeal is considered completed if it is withdrawn or if it has a decision date and outcome set
        return !empty($this->getWithdrawnDate()) ||
            (!empty($this->getDecisionDate()) && !empty($this->getOutcome()));
    }
}
