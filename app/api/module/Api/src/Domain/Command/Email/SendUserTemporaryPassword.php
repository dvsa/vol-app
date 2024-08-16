<?php

/**
 * Send User Temporary Password Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Send User Temporary Password Email
 */
final class SendUserTemporaryPassword extends AbstractCommand
{
    use User;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":2, "max":18})
     */
    protected $password;

    public function getPassword()
    {
        return $this->password;
    }
}
