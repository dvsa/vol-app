<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/annual-test-history/single")
 * @Transfer\Method("PUT")
 */
class UpdateAnnualTestHistory extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Cases;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":4000})
     * @Transfer\Optional
     */
    protected $annualTestHistory = null;

    /**
     * @return string
     */
    public function getAnnualTestHistory()
    {
        return $this->annualTestHistory;
    }
}
