<?php

/**
 * Update ProposeToRevokeSla
 */

namespace Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/propose-to-revoke-sla/single")
 * @Transfer\Method("PUT")
 */
final class UpdateProposeToRevokeSla extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {1, 0}})
     */
    protected $isSubmissionRequiredForApproval;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $approvalSubmissionIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $approvalSubmissionReturnedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $approvalSubmissionPresidingTc;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $iorLetterIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $operatorResponseDueDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $operatorResponseReceivedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {1, 0}})
     */
    protected $isSubmissionRequiredForAction;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $finalSubmissionIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $finalSubmissionReturnedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $finalSubmissionPresidingTc;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {
     *          "ptr_action_to_be_taken_revoke",
     *          "ptr_action_to_be_taken_pi",
     *          "ptr_action_to_be_taken_warning",
     *          "ptr_action_to_be_taken_nfa",
     *          "ptr_action_to_be_taken_other"
     *      }}
     * )
     *
     */
    protected $actionToBeTaken;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $revocationLetterIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $nfaLetterIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $warningLetterIssuedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $piAgreedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $otherActionAgreedDate;


    /**
     * Getter ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter version
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *  Getter IsSubmissionRequiredForApproval
     *
     * @return mixed
     */
    public function getIsSubmissionRequiredForApproval()
    {
        return $this->isSubmissionRequiredForApproval;
    }

    /**
     *  Getter ApprovalSubmissionIssuedDate
     *
     * @return mixed
     */
    public function getApprovalSubmissionIssuedDate()
    {
        return $this->approvalSubmissionIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getApprovalSubmissionReturnedDate()
    {
        return $this->approvalSubmissionReturnedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getApprovalSubmissionPresidingTc()
    {
        return $this->approvalSubmissionPresidingTc;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getIorLetterIssuedDate()
    {
        return $this->iorLetterIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getOperatorResponseDueDate()
    {
        return $this->operatorResponseDueDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getOperatorResponseReceivedDate()
    {
        return $this->operatorResponseReceivedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getIsSubmissionRequiredForAction()
    {
        return $this->isSubmissionRequiredForAction;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getFinalSubmissionIssuedDate()
    {
        return $this->finalSubmissionIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getFinalSubmissionReturnedDate()
    {
        return $this->finalSubmissionReturnedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getFinalSubmissionPresidingTc()
    {
        return $this->finalSubmissionPresidingTc;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getActionToBeTaken()
    {
        return $this->actionToBeTaken;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getRevocationLetterIssuedDate()
    {
        return $this->revocationLetterIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getNfaLetterIssuedDate()
    {
        return $this->nfaLetterIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getWarningLetterIssuedDate()
    {
        return $this->warningLetterIssuedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getPiAgreedDate()
    {
        return $this->piAgreedDate;
    }

    /**
     * Getter
     *
     * @return mixed
     */
    public function getOtherActionAgreedDate()
    {
        return $this->otherActionAgreedDate;
    }
}
