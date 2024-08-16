<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Stay Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="stay",
 *    indexes={
 *        @ORM\Index(name="ix_stay_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_stay_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_stay_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_stay_outcome", columns={"outcome"}),
 *        @ORM\Index(name="ix_stay_stay_type", columns={"stay_type"})
 *    }
 * )
 */
class Stay extends AbstractStay
{
    /**
     * Stay constructor.
     *
     * @param Cases   $case     Case entity
     * @param RefData $stayType Stay Type entity
     */
    public function __construct(Cases $case, RefData $stayType)
    {
        $this->setCase($case);
        $this->setStayType($stayType);
    }

    /**
     * Set values for the Stay entity
     *
     * @param \DateTime|null $requestDate    Request date
     * @param string|null    $decisionDate   Decision date
     * @param RefData|null   $outcome        Outcome of Stay
     * @param string|null    $notes          Notes for stay
     * @param int|null       $isWithdrawn    Is stay withdrawn
     * @param string|null    $withdrawnDate  Withdrawn date
     * @param int|null       $isDvsaNotified Is DVSA notified?
     *
     * @return $this
     */
    public function values(
        $requestDate = null,
        $decisionDate = null,
        $outcome = null,
        $notes = null,
        $isWithdrawn = null,
        $withdrawnDate = null,
        $isDvsaNotified = null
    ) {
        // The logic to check this is in the CommandHandler (CreateStay/UpdateStay)
        $this->setOutcome($outcome);

        $this->setRequestDate($requestDate);

        $decisionDate = (is_null($decisionDate)) ? null : new \DateTime($decisionDate);
        $this->setDecisionDate($decisionDate);

        $this->setNotes($notes);
        $this->setDvsaNotified($isDvsaNotified);

        if ($isWithdrawn === 'Y' && $withdrawnDate !== null) {
            $withdrawnDate = new \DateTime($withdrawnDate);
            $this->setWithdrawnDate($withdrawnDate);
        } else {
            $this->setWithdrawnDate(null);
        }

        return $this;
    }

    /**
     * Is the stay outstanding. Dealt with if outcome or decision date set. Or if its been withdrawn.
     *
     * @return bool
     */
    public function isOutstanding()
    {
        // Stay is considered completed if it is withdrawn or if it has a decision date and outcome set
        return !empty($this->getWithdrawnDate()) ||
        (!empty($this->getDecisionDate()) && !empty($this->getOutcome()));
    }
}
