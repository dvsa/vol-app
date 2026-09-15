<?php

/**
 * Create Submission
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/submission")
 * @Transfer\Method("POST")
 */
final class CreateSubmission extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case;

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
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\SubmissionSection")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $sections = [];

    /**
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

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
}
