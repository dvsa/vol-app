<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Publication;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByApplication;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByLicence;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Class PreviousApplicationPublicationNo
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class PreviousApplicationPublicationNo extends AbstractContext
{
    private static $bundle = [];

    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $params = [
            'pubType' => $publication->getPublication()->getPubType(),
            'trafficArea' => $publication->getTrafficArea(),
            'publicationNo' => $publication->getPublication()->getPublicationNo(),
            'bundle' => self::$bundle
        ];

        $checkByLicence = [
            ApplicationEntity::APPLICATION_STATUS_GRANTED,
            ApplicationEntity::APPLICATION_STATUS_REFUSED,
            ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP,
            ApplicationEntity::APPLICATION_STATUS_CURTAILED
        ];

        $appStatus = $publication->getApplication()->getStatus()->getId();

        //these statuses we check by licence id
        if (in_array($appStatus, $checkByLicence)) {
            $params['licence'] = $publication->getLicence()->getId();
            $query = PreviousPublicationByLicence::create($params);
        } else {
            $params['application'] = $publication->getApplication()->getId();
            $query = PreviousPublicationByApplication::create($params);
        }

        /** @var PublicationLink $previousPublication */
        $previousPublication = $this->handleQuery($query);

        if ($previousPublication instanceof PublicationLink) {
            $context->offsetSet('previousPublication', $previousPublication->getPublication()->getPublicationNo());
        }

        return $context;
    }
}
