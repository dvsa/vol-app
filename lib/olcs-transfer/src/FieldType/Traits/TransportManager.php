<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait TransportManager
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait TransportManager
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManager;

    /**
     * @return int
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }
}
