<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Query\Application\Interim;

/**
 * Class InterimAuthority
 *
 * @package Olcs\Validator
 */
class InterimVehicleAuthority extends AbstractValidator
{
    const GREATER_THAN = "overMax";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::GREATER_THAN      => "The vehicle authority has exceeded the maximum vehicle authority",
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

        if($this->getValue() > $context['totAuthVehicles'])
        {
            $this->error(self::GREATER_THAN);
            return false;
        }
        return true;
    }
}
