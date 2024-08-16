<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Licence No
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class LicenceNo extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $licenceNo = '';
        $licence = $publicationLink->getLicence();

        if (!$licence instanceof LicenceEntity) {
            $application = $publicationLink->getApplication();
            if ($application instanceof ApplicationEntity) {
                $licence = $application->getLicence();
            }
        }

        if ($licence instanceof LicenceEntity) {
            $licenceNo = $licence->getLicNo();
        }

        $context->offsetSet('licenceNo', $licenceNo);
    }
}
