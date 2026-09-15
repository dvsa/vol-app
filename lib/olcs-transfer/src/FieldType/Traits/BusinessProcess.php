<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Business Process Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait BusinessProcess
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"app_business_process_apg", "app_business_process_apgg", "app_business_process_apsg", "app_business_process_ag"}})
     */
    public $businessProcess;

    public function getBusinessProcess()
    {
        return $this->businessProcess;
    }
}
