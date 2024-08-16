<?php

/**
 * Send Tm User Created Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Send Tm User Created Email
 */
final class SendTmUserCreated extends AbstractCommand
{
    use User;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $tma;

    /**
     * @return int
     */
    public function getTma()
    {
        return $this->tma;
    }
}
