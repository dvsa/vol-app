<?php

/**
 * Addresses (Licence & Variation behaviour)
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\Service\Entity\ContactDetailsEntityService;
use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;
use Common\BusinessService\Response;

/**
 * Addresses (Licence & Variation behaviour)
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationAddresses implements
    BusinessServiceInterface,
    BusinessServiceAwareInterface
{
    use BusinessServiceAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $response = $this->getBusinessServiceManager()
            ->get('Lva\ApplicationAddresses')
            ->process($params);

        if (!$response->isOk()) {
            return $response;
        }

        if ($response->getData()['hasChanged']) {
            $taskResponse = $this->getBusinessServiceManager()->get('Lva\AddressesChangeTask')
                ->process($params);

            if (!$taskResponse->isOk()) {
                return $taskResponse;
            }
        }

        return $response;
    }
}
