<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Doctrine\ORM\Mapping as ORM;

/**
 * TmCaseDecision Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="tm_case_decision",
 *    indexes={
 *        @ORM\Index(name="ix_tm_case_decision_decision", columns={"decision"}),
 *        @ORM\Index(name="ix_tm_case_decision_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_tm_case_decision_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_tm_case_decision_case_id", columns={"case_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_tm_case_decision_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class TmCaseDecision extends AbstractTmCaseDecision
{
    public const DECISION_DECLARE_UNFIT = 'tm_decision_rl';
    public const DECISION_NO_FURTHER_ACTION = 'tm_decision_noa';
    public const DECISION_REPUTE_NOT_LOST = 'tm_decision_rnl';

    public function __construct(CasesEntity $case, RefData $decision)
    {
        parent::__construct();
        $this->setCase($case);
        $this->setDecision($decision);
    }

    /**
     * @param array $data Array of data
     * @return TmCaseDecision
     */
    public static function create(CasesEntity $case, RefData $decision, array $data)
    {
        $tmCaseDecision = new self($case, $decision);
        $tmCaseDecision->update($data);

        return $tmCaseDecision;
    }

    /**
     * @param array $data Array of data
     */
    public function update(array $data)
    {
        // validate
        if (
            ($data['notifiedDate'] !== null)
            && (new \DateTime($data['notifiedDate']) < new \DateTime($data['decisionDate']))
        ) {
            throw new ValidationException(['Date of notification must be after or the same as date of decision']);
        }

        // update common properties
        $this->setIsMsi($data['isMsi']);
        $this->setDecisionDate(new \DateTime($data['decisionDate']));

        if ($data['notifiedDate'] !== null) {
            $this->setNotifiedDate(new \DateTime($data['notifiedDate']));
        }

        // each decision may have different update
        match ($this->getDecision()->getId()) {
            self::DECISION_REPUTE_NOT_LOST => $this->updateReputeNotLost($data),
            self::DECISION_NO_FURTHER_ACTION => $this->updateNoFurtherAction($data),
            self::DECISION_DECLARE_UNFIT => $this->updateDeclareUnfit($data),
            default => $this,
        };

        return $this;
    }

    /**
     * @param array $data Array of data
     */
    private function updateReputeNotLost(array $data)
    {
        if ($data['reputeNotLostReason'] !== null) {
            $this->setReputeNotLostReason($data['reputeNotLostReason']);
        }
    }

    /**
     * @param array $data Array of data
     */
    private function updateNoFurtherAction(array $data)
    {
        if ($data['noFurtherActionReason'] !== null) {
            $this->setNoFurtherActionReason($data['noFurtherActionReason']);
        }
    }

    /**
     * @param array $data Array of data
     */
    private function updateDeclareUnfit(array $data)
    {
        // validate
        if (
            ($data['unfitnessEndDate'] !== null)
            && (new \DateTime($data['unfitnessEndDate']) < new \DateTime($data['unfitnessStartDate']))
        ) {
            throw new ValidationException(['Unfitness end date must be after or the same as unfitness start date']);
        }

        // update
        $this->setUnfitnessStartDate(new \DateTime($data['unfitnessStartDate']));

        if ($data['unfitnessEndDate'] !== null) {
            $this->setUnfitnessEndDate(new \DateTime($data['unfitnessEndDate']));
        }

        $this->setUnfitnessReasons($data['unfitnessReasons']);

        if ($data['rehabMeasures'] !== null) {
            $this->setRehabMeasures($data['rehabMeasures']);
        }
    }
}
