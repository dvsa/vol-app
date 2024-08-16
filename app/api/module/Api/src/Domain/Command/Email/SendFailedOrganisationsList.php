<?php

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

class SendFailedOrganisationsList extends AbstractCommand
{
    /**
     * @var string[]
     */
    protected $organisationNumbers;

    /**
     * @var string
     */
    protected $emailAddress;

    /**
     * @var string
     */
    protected $emailSubject;

    /**
     * @return string[]
     */
    public function getOrganisationNumbers(): array
    {
        return $this->organisationNumbers;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return mixed
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }
}
