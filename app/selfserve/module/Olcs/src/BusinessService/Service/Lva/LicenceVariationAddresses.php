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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Addresses (Licence & Variation behaviour)
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationAddresses implements
    BusinessServiceInterface,
    BusinessServiceAwareInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait,
        BusinessServiceAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $dirtyResponse = $this->getBusinessServiceManager()
            ->get('Lva\DirtyAddresses')
            ->process(
                [
                    'original' => $params['originalData'],
                    'updated'  => $params['data']
                ]
            );

        $hasChanged = $dirtyResponse->getData()['dirtyFieldsets'] > 0;

        if ($hasChanged) {
            $response = $this->getBusinessServiceManager()->get('Lva\AddressesChangeTask')
                ->process($params);

            if (!$response->isOk()) {
                return $response;
            }
        }

        $response = $this->getBusinessServiceManager()
            ->get('Lva\ApplicationAddresses')
            ->process($params);

        if (!$response->isOk()) {
            return $response;
        }

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);
        $response->setData(
            [
                'hasChanged' => $hasChanged
            ]
        );

        return $response;
    }
}
