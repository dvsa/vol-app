<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * ProposeToRevoke Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="propose_to_revoke",
 *    indexes={
 *        @ORM\Index(name="ix_propose_to_revoke_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_propose_to_revoke_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="ix_propose_to_revoke_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_propose_to_revoke_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class ProposeToRevoke extends AbstractProposeToRevoke
{
    public const PTR_ACTION_TO_BE_TAKEN_REVOKE = 'ptr_action_to_be_taken_revoke';
    public const PTR_ACTION_TO_BE_TAKEN_PI = 'ptr_action_to_be_taken_pi';
    public const PTR_ACTION_TO_BE_TAKEN_WARNING = 'ptr_action_to_be_taken_warning';
    public const PTR_ACTION_TO_BE_TAKEN_NFA = 'ptr_action_to_be_taken_nfa';
    public const PTR_ACTION_TO_BE_TAKEN_OTHER = 'ptr_action_to_be_taken_other';

    /**
     * ProposeToRevoke constructor.
     *
     * @param Cases       $case               case
     * @param array       $reasons            reasons
     * @param PresidingTc $presidingTc        presidingTc
     * @param \DateTime   $ptrAgreedDate      ptrAgreedDate
     * @param User|null   $assignedCaseworker assignedCaseworker
     */
    public function __construct(
        Cases $case,
        array $reasons,
        PresidingTc $presidingTc,
        \DateTime $ptrAgreedDate,
        User $assignedCaseworker = null
    ) {
        parent::__construct();
        $this->case = $case;
        $this->reasons = $reasons;
        $this->presidingTc = $presidingTc;
        $this->ptrAgreedDate = $ptrAgreedDate;
        $this->assignedCaseworker = $assignedCaseworker;
    }

    /**
     * Update
     *
     * @param array       $reasons            reasons
     * @param PresidingTc $presidingTc        presidingTc
     * @param \DateTime   $ptrAgreedDate      ptrAgreedDate
     * @param User|null   $assignedCaseworker assignedCaseworker
     * @return void
     */
    public function update(
        array $reasons,
        PresidingTc $presidingTc,
        \DateTime $ptrAgreedDate,
        User $assignedCaseworker = null
    ) {
        $this->reasons = $reasons;
        $this->presidingTc = $presidingTc;
        $this->ptrAgreedDate = $ptrAgreedDate;
        $this->assignedCaseworker = $assignedCaseworker;
    }
}
