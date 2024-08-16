<?php

/**
 * Community Licence / Create Office Copy
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\CommunityLic\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Community Licence / Create Office Copy
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateOfficeCopy extends AbstractCommand
{
    public $licence;

    public $identifier;

    public function getLicence()
    {
        return $this->licence;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
