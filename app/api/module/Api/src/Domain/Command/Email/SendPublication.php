<?php

/**
 * Send a publication
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send a publication
 */
final class SendPublication extends AbstractIdOnlyCommand
{
    /**
     * @var string
     */
    protected $isPolice;

    /**
     * Whether to send the police version Y or N
     *
     * @return string
     */
    public function getIsPolice()
    {
        return $this->isPolice;
    }
}
