<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Case Categorys
 */
trait CaseCategorys
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     */
    protected $categorys = [];

    /**
     * @return array
     */
    public function getCategorys()
    {
        return $this->categorys;
    }
}
