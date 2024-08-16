<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Publication;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByPi;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * Class PreviousPublicationNo
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class PreviousPublicationNo extends AbstractContext
{
    private static $bundle = [];

    /**
     * Provide
     *
     * @param PublicationLink $publication publication
     * @param \ArrayObject    $context     context
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $params = [
            'pi' => $publication->getPi()->getId(),
            'pubType' => $publication->getPublication()->getPubType(),
            'trafficArea' => $publication->getTrafficArea(),
            'publicationNo' => $publication->getPublication()->getPublicationNo(),
            'bundle' => self::$bundle
        ];

        $query = PreviousPublicationByPi::create($params);

        /** @var PublicationLink $previousPublication */
        $previousPublication = $this->handleQuery($query);

        if (!empty($previousPublication)) {
            $pp = $previousPublication->serialize();
            $context->offsetSet('previousPublication', $pp['publication']['publicationNo']);
        }

        return $context;
    }
}
