<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractProposeToRevoke Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="propose_to_revoke",
 *    indexes={
 *        @ORM\Index(name="ix_propose_to_revoke_action_to_be_taken", columns={"action_to_be_taken"}),
 *        @ORM\Index(name="ix_propose_to_revoke_approval_submission_presiding_tc", columns={"approval_submission_presiding_tc"}),
 *        @ORM\Index(name="ix_propose_to_revoke_assigned_caseworker", columns={"assigned_caseworker"}),
 *        @ORM\Index(name="ix_propose_to_revoke_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_propose_to_revoke_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_propose_to_revoke_final_submission_presiding_tc", columns={"final_submission_presiding_tc"}),
 *        @ORM\Index(name="ix_propose_to_revoke_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_propose_to_revoke_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="uk_propose_to_revoke_case_id", columns={"case_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_propose_to_revoke_case_id", columns={"case_id"})
 *    }
 * )
 */
abstract class AbstractProposeToRevoke implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * Foreign Key to presiding_tc
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\PresidingTc", fetch="LAZY")
     * @ORM\JoinColumn(name="presiding_tc_id", referencedColumnName="id")
     */
    protected $presidingTc;

    /**
     * AssignedCaseworker
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="assigned_caseworker", referencedColumnName="id", nullable=true)
     */
    protected $assignedCaseworker;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * ApprovalSubmissionPresidingTc
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\PresidingTc", fetch="LAZY")
     * @ORM\JoinColumn(name="approval_submission_presiding_tc", referencedColumnName="id", nullable=true)
     */
    protected $approvalSubmissionPresidingTc;

    /**
     * FinalSubmissionPresidingTc
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\PresidingTc", fetch="LAZY")
     * @ORM\JoinColumn(name="final_submission_presiding_tc", referencedColumnName="id", nullable=true)
     */
    protected $finalSubmissionPresidingTc;

    /**
     * ActionToBeTaken
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="action_to_be_taken", referencedColumnName="id", nullable=true)
     */
    protected $actionToBeTaken;

    /**
     * Ptr agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="ptr_agreed_date", nullable=true)
     */
    protected $ptrAgreedDate;

    /**
     * Closed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="closed_date", nullable=true)
     */
    protected $closedDate;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="comment", length=4000, nullable=true)
     */
    protected $comment;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Is submission required for approval
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_submission_required_for_approval", nullable=true)
     */
    protected $isSubmissionRequiredForApproval;

    /**
     * Approval submission issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="approval_submission_issued_date", nullable=true)
     */
    protected $approvalSubmissionIssuedDate;

    /**
     * Approval submission returned date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="approval_submission_returned_date", nullable=true)
     */
    protected $approvalSubmissionReturnedDate;

    /**
     * Ior letter issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="ior_letter_issued_date", nullable=true)
     */
    protected $iorLetterIssuedDate;

    /**
     * Operator response due date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="operator_response_due_date", nullable=true)
     */
    protected $operatorResponseDueDate;

    /**
     * Operator response received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="operator_response_received_date", nullable=true)
     */
    protected $operatorResponseReceivedDate;

    /**
     * Is submission required for action
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_submission_required_for_action", nullable=true)
     */
    protected $isSubmissionRequiredForAction;

    /**
     * Final submission issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="final_submission_issued_date", nullable=true)
     */
    protected $finalSubmissionIssuedDate;

    /**
     * Final submission returned date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="final_submission_returned_date", nullable=true)
     */
    protected $finalSubmissionReturnedDate;

    /**
     * Revocation letter issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="revocation_letter_issued_date", nullable=true)
     */
    protected $revocationLetterIssuedDate;

    /**
     * Nfa letter issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="nfa_letter_issued_date", nullable=true)
     */
    protected $nfaLetterIssuedDate;

    /**
     * Warning letter issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="warning_letter_issued_date", nullable=true)
     */
    protected $warningLetterIssuedDate;

    /**
     * Pi agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="pi_agreed_date", nullable=true)
     */
    protected $piAgreedDate;

    /**
     * Other action agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="other_action_agreed_date", nullable=true)
     */
    protected $otherActionAgreedDate;

    /**
     * Reasons
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Reason", inversedBy="proposeToRevokes", fetch="LAZY")
     * @ORM\JoinTable(name="ptr_reason",
     *     joinColumns={
     *         @ORM\JoinColumn(name="propose_to_revoke_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="reason_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $reasons;

    /**
     * SlaTargetDates
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\SlaTargetDate", mappedBy="proposeToRevoke", cascade={"persist"}, indexBy="sla_id", orphanRemoval=true)
     */
    protected $slaTargetDates;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
        $this->reasons = new ArrayCollection();
        $this->slaTargetDates = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ProposeToRevoke
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return ProposeToRevoke
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the presiding tc
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\PresidingTc $presidingTc new value being set
     *
     * @return ProposeToRevoke
     */
    public function setPresidingTc($presidingTc)
    {
        $this->presidingTc = $presidingTc;

        return $this;
    }

    /**
     * Get the presiding tc
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\PresidingTc     */
    public function getPresidingTc()
    {
        return $this->presidingTc;
    }

    /**
     * Set the assigned caseworker
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $assignedCaseworker new value being set
     *
     * @return ProposeToRevoke
     */
    public function setAssignedCaseworker($assignedCaseworker)
    {
        $this->assignedCaseworker = $assignedCaseworker;

        return $this;
    }

    /**
     * Get the assigned caseworker
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getAssignedCaseworker()
    {
        return $this->assignedCaseworker;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return ProposeToRevoke
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return ProposeToRevoke
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the approval submission presiding tc
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\PresidingTc $approvalSubmissionPresidingTc new value being set
     *
     * @return ProposeToRevoke
     */
    public function setApprovalSubmissionPresidingTc($approvalSubmissionPresidingTc)
    {
        $this->approvalSubmissionPresidingTc = $approvalSubmissionPresidingTc;

        return $this;
    }

    /**
     * Get the approval submission presiding tc
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\PresidingTc     */
    public function getApprovalSubmissionPresidingTc()
    {
        return $this->approvalSubmissionPresidingTc;
    }

    /**
     * Set the final submission presiding tc
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\PresidingTc $finalSubmissionPresidingTc new value being set
     *
     * @return ProposeToRevoke
     */
    public function setFinalSubmissionPresidingTc($finalSubmissionPresidingTc)
    {
        $this->finalSubmissionPresidingTc = $finalSubmissionPresidingTc;

        return $this;
    }

    /**
     * Get the final submission presiding tc
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\PresidingTc     */
    public function getFinalSubmissionPresidingTc()
    {
        return $this->finalSubmissionPresidingTc;
    }

    /**
     * Set the action to be taken
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $actionToBeTaken new value being set
     *
     * @return ProposeToRevoke
     */
    public function setActionToBeTaken($actionToBeTaken)
    {
        $this->actionToBeTaken = $actionToBeTaken;

        return $this;
    }

    /**
     * Get the action to be taken
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getActionToBeTaken()
    {
        return $this->actionToBeTaken;
    }

    /**
     * Set the ptr agreed date
     *
     * @param \DateTime $ptrAgreedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setPtrAgreedDate($ptrAgreedDate)
    {
        $this->ptrAgreedDate = $ptrAgreedDate;

        return $this;
    }

    /**
     * Get the ptr agreed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPtrAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->ptrAgreedDate);
        }

        return $this->ptrAgreedDate;
    }

    /**
     * Set the closed date
     *
     * @param \DateTime $closedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get the closed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getClosedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->closedDate);
        }

        return $this->closedDate;
    }

    /**
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return ProposeToRevoke
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ProposeToRevoke
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the is submission required for approval
     *
     * @param bool $isSubmissionRequiredForApproval new value being set
     *
     * @return ProposeToRevoke
     */
    public function setIsSubmissionRequiredForApproval($isSubmissionRequiredForApproval)
    {
        $this->isSubmissionRequiredForApproval = $isSubmissionRequiredForApproval;

        return $this;
    }

    /**
     * Get the is submission required for approval
     *
     * @return bool     */
    public function getIsSubmissionRequiredForApproval()
    {
        return $this->isSubmissionRequiredForApproval;
    }

    /**
     * Set the approval submission issued date
     *
     * @param \DateTime $approvalSubmissionIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setApprovalSubmissionIssuedDate($approvalSubmissionIssuedDate)
    {
        $this->approvalSubmissionIssuedDate = $approvalSubmissionIssuedDate;

        return $this;
    }

    /**
     * Get the approval submission issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getApprovalSubmissionIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->approvalSubmissionIssuedDate);
        }

        return $this->approvalSubmissionIssuedDate;
    }

    /**
     * Set the approval submission returned date
     *
     * @param \DateTime $approvalSubmissionReturnedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setApprovalSubmissionReturnedDate($approvalSubmissionReturnedDate)
    {
        $this->approvalSubmissionReturnedDate = $approvalSubmissionReturnedDate;

        return $this;
    }

    /**
     * Get the approval submission returned date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getApprovalSubmissionReturnedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->approvalSubmissionReturnedDate);
        }

        return $this->approvalSubmissionReturnedDate;
    }

    /**
     * Set the ior letter issued date
     *
     * @param \DateTime $iorLetterIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setIorLetterIssuedDate($iorLetterIssuedDate)
    {
        $this->iorLetterIssuedDate = $iorLetterIssuedDate;

        return $this;
    }

    /**
     * Get the ior letter issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getIorLetterIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->iorLetterIssuedDate);
        }

        return $this->iorLetterIssuedDate;
    }

    /**
     * Set the operator response due date
     *
     * @param \DateTime $operatorResponseDueDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setOperatorResponseDueDate($operatorResponseDueDate)
    {
        $this->operatorResponseDueDate = $operatorResponseDueDate;

        return $this;
    }

    /**
     * Get the operator response due date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOperatorResponseDueDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->operatorResponseDueDate);
        }

        return $this->operatorResponseDueDate;
    }

    /**
     * Set the operator response received date
     *
     * @param \DateTime $operatorResponseReceivedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setOperatorResponseReceivedDate($operatorResponseReceivedDate)
    {
        $this->operatorResponseReceivedDate = $operatorResponseReceivedDate;

        return $this;
    }

    /**
     * Get the operator response received date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOperatorResponseReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->operatorResponseReceivedDate);
        }

        return $this->operatorResponseReceivedDate;
    }

    /**
     * Set the is submission required for action
     *
     * @param bool $isSubmissionRequiredForAction new value being set
     *
     * @return ProposeToRevoke
     */
    public function setIsSubmissionRequiredForAction($isSubmissionRequiredForAction)
    {
        $this->isSubmissionRequiredForAction = $isSubmissionRequiredForAction;

        return $this;
    }

    /**
     * Get the is submission required for action
     *
     * @return bool     */
    public function getIsSubmissionRequiredForAction()
    {
        return $this->isSubmissionRequiredForAction;
    }

    /**
     * Set the final submission issued date
     *
     * @param \DateTime $finalSubmissionIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setFinalSubmissionIssuedDate($finalSubmissionIssuedDate)
    {
        $this->finalSubmissionIssuedDate = $finalSubmissionIssuedDate;

        return $this;
    }

    /**
     * Get the final submission issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getFinalSubmissionIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->finalSubmissionIssuedDate);
        }

        return $this->finalSubmissionIssuedDate;
    }

    /**
     * Set the final submission returned date
     *
     * @param \DateTime $finalSubmissionReturnedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setFinalSubmissionReturnedDate($finalSubmissionReturnedDate)
    {
        $this->finalSubmissionReturnedDate = $finalSubmissionReturnedDate;

        return $this;
    }

    /**
     * Get the final submission returned date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getFinalSubmissionReturnedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->finalSubmissionReturnedDate);
        }

        return $this->finalSubmissionReturnedDate;
    }

    /**
     * Set the revocation letter issued date
     *
     * @param \DateTime $revocationLetterIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setRevocationLetterIssuedDate($revocationLetterIssuedDate)
    {
        $this->revocationLetterIssuedDate = $revocationLetterIssuedDate;

        return $this;
    }

    /**
     * Get the revocation letter issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRevocationLetterIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->revocationLetterIssuedDate);
        }

        return $this->revocationLetterIssuedDate;
    }

    /**
     * Set the nfa letter issued date
     *
     * @param \DateTime $nfaLetterIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setNfaLetterIssuedDate($nfaLetterIssuedDate)
    {
        $this->nfaLetterIssuedDate = $nfaLetterIssuedDate;

        return $this;
    }

    /**
     * Get the nfa letter issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getNfaLetterIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->nfaLetterIssuedDate);
        }

        return $this->nfaLetterIssuedDate;
    }

    /**
     * Set the warning letter issued date
     *
     * @param \DateTime $warningLetterIssuedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setWarningLetterIssuedDate($warningLetterIssuedDate)
    {
        $this->warningLetterIssuedDate = $warningLetterIssuedDate;

        return $this;
    }

    /**
     * Get the warning letter issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getWarningLetterIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->warningLetterIssuedDate);
        }

        return $this->warningLetterIssuedDate;
    }

    /**
     * Set the pi agreed date
     *
     * @param \DateTime $piAgreedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setPiAgreedDate($piAgreedDate)
    {
        $this->piAgreedDate = $piAgreedDate;

        return $this;
    }

    /**
     * Get the pi agreed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPiAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->piAgreedDate);
        }

        return $this->piAgreedDate;
    }

    /**
     * Set the other action agreed date
     *
     * @param \DateTime $otherActionAgreedDate new value being set
     *
     * @return ProposeToRevoke
     */
    public function setOtherActionAgreedDate($otherActionAgreedDate)
    {
        $this->otherActionAgreedDate = $otherActionAgreedDate;

        return $this;
    }

    /**
     * Get the other action agreed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOtherActionAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->otherActionAgreedDate);
        }

        return $this->otherActionAgreedDate;
    }

    /**
     * Set the reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reasons collection being set as the value
     *
     * @return ProposeToRevoke
     */
    public function setReasons($reasons)
    {
        $this->reasons = $reasons;

        return $this;
    }

    /**
     * Get the reasons
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReasons()
    {
        return $this->reasons;
    }

    /**
     * Add a reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $reasons collection being added
     *
     * @return ProposeToRevoke
     */
    public function addReasons($reasons)
    {
        if ($reasons instanceof ArrayCollection) {
            $this->reasons = new ArrayCollection(
                array_merge(
                    $this->reasons->toArray(),
                    $reasons->toArray()
                )
            );
        } elseif (!$this->reasons->contains($reasons)) {
            $this->reasons->add($reasons);
        }

        return $this;
    }

    /**
     * Remove a reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reasons collection being removed
     *
     * @return ProposeToRevoke
     */
    public function removeReasons($reasons)
    {
        if ($this->reasons->contains($reasons)) {
            $this->reasons->removeElement($reasons);
        }

        return $this;
    }

    /**
     * Set the sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $slaTargetDates collection being set as the value
     *
     * @return ProposeToRevoke
     */
    public function setSlaTargetDates($slaTargetDates)
    {
        $this->slaTargetDates = $slaTargetDates;

        return $this;
    }

    /**
     * Get the sla target dates
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSlaTargetDates()
    {
        return $this->slaTargetDates;
    }

    /**
     * Add a sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $slaTargetDates collection being added
     *
     * @return ProposeToRevoke
     */
    public function addSlaTargetDates($slaTargetDates)
    {
        if ($slaTargetDates instanceof ArrayCollection) {
            $this->slaTargetDates = new ArrayCollection(
                array_merge(
                    $this->slaTargetDates->toArray(),
                    $slaTargetDates->toArray()
                )
            );
        } elseif (!$this->slaTargetDates->contains($slaTargetDates)) {
            $this->slaTargetDates->add($slaTargetDates);
        }

        return $this;
    }

    /**
     * Remove a sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $slaTargetDates collection being removed
     *
     * @return ProposeToRevoke
     */
    public function removeSlaTargetDates($slaTargetDates)
    {
        if ($this->slaTargetDates->contains($slaTargetDates)) {
            $this->slaTargetDates->removeElement($slaTargetDates);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}