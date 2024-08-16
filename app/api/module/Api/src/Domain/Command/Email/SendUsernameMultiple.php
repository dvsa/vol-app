<?php

/**
 * Send Username Multiple Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Send Username Multiple Email
 */
final class SendUsernameMultiple extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":2, "max":18})
     */
    protected $licenceNumber;

    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }
}
