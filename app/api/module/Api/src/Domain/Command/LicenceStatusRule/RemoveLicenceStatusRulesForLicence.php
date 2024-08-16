<?php

/**
 * RemoveLicenceStatusRulesForLicence.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class RemoveLicenceStatusRulesForLicence extends AbstractCommand
{
    protected $licence;

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }
}
