<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * FeatureToggleConfigName
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait FeatureToggleConfigName
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":255})
     */
    protected $configName;

    public function getConfigName(): string
    {
        return $this->configName;
    }
}
