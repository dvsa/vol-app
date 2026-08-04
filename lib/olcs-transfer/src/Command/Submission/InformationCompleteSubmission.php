<?php

/**
 * Complete Submission
 */

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/submission/information-complete")
 * @Transfer\Method("PUT")
 */
final class InformationCompleteSubmission extends AbstractCommand
{
    // Identity & Locking
    use FieldType\Traits\Identity;
    use FieldType\Traits\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateNotInFuture")
     */
    protected $informationCompleteDate = null;

    /**
     * @return mixed
     */
    public function getInformationCompleteDate()
    {
        return $this->informationCompleteDate;
    }
}
