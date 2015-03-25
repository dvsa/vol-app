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

    /**
     * Save correspondence details
     *
     * @param array $data
     * @return array
     */
    protected function saveCorrespondenceDetails($licenceId, $data)
    {
        return $this->saveAddressToLicence(
            $licenceId,
            $data,
            ContactDetailsEntityService::CONTACT_TYPE_CORRESPONDENCE,
            'correspondence',
            [
                'fao' => $data['correspondence']['fao'],
                'emailAddress' => $data['contact']['email'],
            ]
        );
    }

    protected function saveAddressToLicence($licenceId, $data, $contactType, $type, $additionalData = array())
    {
        $address = array(
            'id' => $data[$type]['id'],
            'version' => $data[$type]['version'],
            'contactType' => $contactType,
            'addresses' => array(
                'address' => $data[$type . '_address'],
            )
        );

        $address = array_merge($address, $additionalData);

        $response = $this->getServiceLocator()
            ->get('BusinessServiceManager')
            ->get('Lva\ContactDetails')
            ->process(
                [
                    'data' => $address
                ]
            );

        if (!$response->isOk()) {
            return $response;
        }

        // If we are creating a new contactDetails item, we need to link it to the licence
        if (!isset($data[$type]['id']) || empty($data[$type]['id'])) {
            $saved = $response->getData();

            $licenceData = [$type . 'Cd' => $saved['id']];

            $this->getServiceLocator()->get('Entity\Licence')->forceUpdate($licenceId, $licenceData);
        }

        return $response;
    }
}
