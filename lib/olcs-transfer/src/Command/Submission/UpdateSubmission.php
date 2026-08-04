<?php

/**
 * Update Submission
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/submission/single")
 * @Transfer\Method("PUT")
 */
final class UpdateSubmission extends AbstractCommand
{
    // Identity & Locking
    use FieldType\Traits\Identity;
    use FieldType\Traits\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "submission_type_o_bus_reg", "submission_type_o_clo_fep", "submission_type_o_clo_g",
     * "submission_type_o_clo_psv", "submission_type_o_env", "submission_type_o_impounding",
     * "submission_type_o_irfo", "submission_type_o_mlh_clo", "submission_type_o_mlh_otc", "submission_type_o_otc",
     * "submission_type_o_schedule_41", "submission_type_o_tm", "submission_type_o_ni_tru"
     *          }
     *      }
     * )
     */
    protected $submissionType;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\SubmissionSection")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $sections = [];

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $senderUser;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $recipientUser;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $urgent;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     * @Transfer\Optional
     */
    protected $closedDate;

    /**
     * @return mixed
     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }

    /**
     * @return mixed
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @return mixed
     */
    public function getSenderUser()
    {
        return $this->senderUser;
    }

    /**
     * @return mixed
     */
    public function getRecipientUser()
    {
        return $this->recipientUser;
    }

    /**
     * @return mixed
     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * @return mixed
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }
}
