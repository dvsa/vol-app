<?php

/**
 * Create SubmissionSectionComment
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EditorJsComment;

/**
 * @Transfer\RouteName("backend/submission-section-comment")
 * @Transfer\Method("POST")
 */
final class CreateSubmissionSectionComment extends AbstractCommand
{
    use EditorJsComment;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $submission;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "introduction", "case-summary","case-outline", "most-serious-infringement",
     *  "outstanding-applications","people","operating-centres","conditions-and-undertakings",
     * "intelligence-unit-check","interim","advertisement","linked-licences-app-numbers","lead-tc-area",
     * "current-submissions","auth-requested-applied-for","transport-managers","continuous-effective-control",
     * "fitness-and-repute","previous-history","bus-reg-app-details","transport-authority-comments",
     * "total-bus-registrations","local-licence-history","linked-mlh-history","registration-details",
     * "maintenance-tachographs-hours","prohibition-history","conviction-fpn-offence-history","annual-test-history",
     * "penalties","statements","other-issues","te-reports","site-plans","planning-permission","applicants-comments",
     * "visibility-access-egress-size","compliance-complaints","environmental-complaints","oppositions",
     * "financial-information","maps","waive-fee-late-fee","surrender","annex","tm-details","tm-qualifications",
     * "tm-responsibilities","tm-other-employment","tm-previous-history","applicants-responses"
     *          }
     *      }
     * )
     */
    protected $submissionSection;

    /**
     * @return mixed
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @return mixed
     */
    public function getSubmissionSection()
    {
        return $this->submissionSection;
    }
}
