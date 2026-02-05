<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class BusNote
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Application
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class BusNote extends AbstractContext
{
    public const string BUS_STRING = 'Registered Bus Services running under this licence have also been %s with immediate effect.';

    public const string BUS_REVOKED = 'revoked';
    public const string BUS_SURRENDERED = 'surrendered';
    public const string BUS_CNS = 'set to CNS';

    /**
     * @param PublicationLink $publication
     * @param \ArrayObject $context
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $licType = $publication->getApplication()->getGoodsOrPsv()->getId();

        if ($licType == LicenceEntity::LICENCE_CATEGORY_PSV) {
            $publicationSection = $publication->getPublicationSection()->getId();

            switch ($publicationSection) {
                case PublicationSectionEntity::LIC_SURRENDERED_SECTION:
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

        return $context;
    }
}
