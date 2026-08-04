<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * FeatureToggleFriendlyName
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait FeatureToggleFriendlyName
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":255})
     */
    protected $friendlyName;

    public function getFriendlyName(): string
    {
        return $this->friendlyName;
    }
}
