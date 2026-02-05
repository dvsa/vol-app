<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;

/**
 * Class BusNote
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class BusNote extends AbstractContext
{
    public const string BUS_STRING = 'Registered Bus Services running under this licence have also been %s with immediate effect.';

    public const string BUS_REVOKED = 'revoked';
    public const string BUS_SURRENDERED = 'surrendered';
    public const string BUS_CNS = 'set to CNS';

    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        if ($publicationLink->getLicence()->isPsv()) {
            $publicationSection = $publicationLink->getPublicationSection()->getId();

            switch ($publicationSection) {
                case PublicationSectionEntity::LIC_TERMINATED_SECTION:
                    $context->offsetSet('busNote', sprintf(self::BUS_STRING, self::BUS_SURRENDERED));
                    break;
                case PublicationSectionEntity::LIC_REVOKED_SECTION:
                    $context->offsetSet('busNote', sprintf(self::BUS_STRING, self::BUS_REVOKED));
                    break;
                case PublicationSectionEntity::LIC_CNS_SECTION:
                    $context->offsetSet('busNote', sprintf(self::BUS_STRING, self::BUS_CNS));
                    break;
            }
        }
    }
}
