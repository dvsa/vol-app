<?php

/**
 * Validating reprint caller
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

class ValidatingReprintCaller extends AbstractCommand
{
    use Licence;
    use User;

    /**
     * @Transfer\ArrayInput
     */
    protected $communityLicences;

    /**
     * Get community licences
     *
     * @return array
     */
    public function getCommunityLicences()
    {
        return $this->communityLicences;
    }
}
