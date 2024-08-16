<?php

namespace Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Processes EBSR pack
 */
class ProcessPackTransaction extends AbstractIdOnlyCommand
{
    protected $organisation;

    /**
     * @return int
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }
}
