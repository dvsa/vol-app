<?php

/**
 * Organisation Provider Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Organisation Provider Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface OrganisationProviderInterface
{
    /**
     * Get the related organisation
     *
     * @return Organisation|Organisation[]
     */
    public function getRelatedOrganisation();
}
