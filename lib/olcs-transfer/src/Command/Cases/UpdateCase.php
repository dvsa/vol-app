<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/cases/single")
 * @Transfer\Method("PUT")
 */
class UpdateCase extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;
    use FieldType\ApplicationOptional;
    use FieldType\LicenceOptional;
    use FieldType\TransportManagerOptional;
    use FieldType\CaseCategorys;
    use FieldType\CaseOutcomesOptional;
    use FieldType\CaseType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":45})
     * @Transfer\Optional
     */
    protected $ecmsNo = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":1024})
     * @Transfer\Optional
     */
    protected $description = null;

    /**
     * @return string
     */
    public function getEcmsNo()
    {
        return $this->ecmsNo;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
