<?php

/**
 * Update Submission sections. Leave any other sections as is. Just update section/subsection passed in Command
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/submission/refresh")
 * @Transfer\Method("PUT")
 */
final class RefreshSubmissionSections extends AbstractCommand
{
    // Identity & Locking
    use FieldType\Traits\Identity;
    use FieldType\Traits\Version;

    /**
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\SubmissionSection")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":50})
     */
    protected $section;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":50})
     */
    protected $subSection;

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return mixed
     */
    public function getSubSection()
    {
        return $this->subSection;
    }
}
