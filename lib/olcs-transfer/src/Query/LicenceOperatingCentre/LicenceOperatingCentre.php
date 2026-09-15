<?php

/**
 * Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\LicenceOperatingCentre;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence-operating-centre/single")
 */
class LicenceOperatingCentre extends AbstractQuery
{
    use Identity;

    /**
     * @Transfer\Optional
     */
    protected $isVariation = false;

    /**
     * @return mixed
     */
    public function getIsVariation()
    {
        return $this->isVariation;
    }
}
