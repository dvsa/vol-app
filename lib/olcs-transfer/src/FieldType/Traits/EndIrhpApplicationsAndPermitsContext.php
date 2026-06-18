<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * End IRHP Applications and Permits Context Trait
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait EndIrhpApplicationsAndPermitsContext
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"context_surrender","context_revoke","context_cns"}})
     */
    protected $context;

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
}
