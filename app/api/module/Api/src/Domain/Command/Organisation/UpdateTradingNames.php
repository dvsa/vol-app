<?php

/**
 * Update Trading Names
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Organisation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update Trading Names
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTradingNames extends AbstractCommand
{
    protected $licence;

    protected $organisation;

    protected $tradingNames;

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return mixed
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return mixed
     */
    public function getTradingNames()
    {
        return $this->tradingNames;
    }
}
