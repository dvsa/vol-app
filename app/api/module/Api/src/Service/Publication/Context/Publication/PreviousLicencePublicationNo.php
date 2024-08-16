<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Publication;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByLicence;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * Class PreviousApplicationPublicationNo
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class PreviousLicencePublicationNo extends AbstractContext
{
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $params = [
            'pubType' => $publicationLink->getPublication()->getPubType(),
            'trafficArea' => $publicationLink->getTrafficArea(),
            'publicationNo' => $publicationLink->getPublication()->getPublicationNo(),
            'licence' => $publicationLink->getLicence()->getId(),
            'bundle' => []
        ];
        $query = PreviousPublicationByLicence::create($params);

        /** @var PublicationLink $previousPublication */
        $previousPublication = $this->handleQuery($query);

        if ($previousPublication instanceof PublicationLink) {
            $context->offsetSet('previousPublication', $previousPublication->getPublication()->getPublicationNo());
        }
    }
}
