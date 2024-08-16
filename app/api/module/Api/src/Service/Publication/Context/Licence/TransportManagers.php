<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * Class TransportManagers
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TransportManagers extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $licenceTransportManagers = [];

        foreach ($publicationLink->getLicence()->getTmLicences() as $tml) {
            /* @var $tml \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence */
            $licenceTransportManagers[] = $tml->getTransportManager();
        }

        // contains an array of TM entities on the licence
        if (!empty($licenceTransportManagers)) {
            $context->offsetSet('licenceTransportManagers', $licenceTransportManagers);
        }
    }
}
