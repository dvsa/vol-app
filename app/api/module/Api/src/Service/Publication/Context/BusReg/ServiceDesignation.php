<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\BusReg;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService;

/**
 * Class ServiceDesignation
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Bus
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class ServiceDesignation extends AbstractContext
{
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $busReg = $publication->getBusReg();
        $services = [$busReg->getServiceNo()];
        $otherServices = $busReg->getOtherServices();

        if (!$otherServices->isEmpty()) {
            foreach ($otherServices as $otherService) {
                /** @var BusRegOtherService $otherService */
                $services[] = $otherService->getServiceNo();
            }
        }

        $context->offsetSet('busServices', implode(' / ', $services));

        return $context;
    }
}
