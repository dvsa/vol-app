<?php

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Licence Provider Interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface LicenceProviderInterface
{
    /**
     * Get the related licence
     *
     * @return Licence
     */
    public function getRelatedLicence(): Licence;
}
