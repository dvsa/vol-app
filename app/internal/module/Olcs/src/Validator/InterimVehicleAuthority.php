<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;

class InterimVehicleAuthority extends AbstractValidator
{
    const VEHICLE_AUTHORITY_EXCEEDED = "vehicleAuthExceeded";
    const VALUE_BELOW_ONE = "valueBelowOne";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::VEHICLE_AUTHORITY_EXCEEDED => "The interim vehicle authority cannot exceed the total vehicle authority",
        self::VALUE_BELOW_ONE            => "The input is not greater or equal than '1'",
    );

    /**
     * Returns true if interim authorised vehicles < total authorised vehicles
     *
     * @param mixed $value
     * @param null  $context
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $totalAuthVehicles = ($context['totAuthVehicles'] == null ? 0 : $context['totAuthVehicles']);

        if ($context['isVariation']) {
            if ($this->getValue() > $totalAuthVehicles) {
                $this->error(self::VEHICLE_AUTHORITY_EXCEEDED);
                return false;
            }
            return true;
        } else {
            if ($this->getValue() > $totalAuthVehicles) {
                $this->error(self::VEHICLE_AUTHORITY_EXCEEDED);
                return false;
            } elseif ($this->getValue() == 0) {
                $this->error(self::VALUE_BELOW_ONE);
                return false;
            }
            return true;
        }
    }
}
