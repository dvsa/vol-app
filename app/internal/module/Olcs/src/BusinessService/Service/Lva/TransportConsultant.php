<?php

/**
 * TransportConsultant.php
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;

use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;

use Common\BusinessService\Response;
use Common\Service\Entity\ContactDetailsEntityService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class TransportConsultant
 *
 * Save a transport consultant contact details against a licence.
 *
 * @package Common\BusinessService\Service\Lva
 */
class TransportConsultant implements
    BusinessServiceInterface,
    BusinessServiceAwareInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait,
        BusinessServiceAwareTrait;

    /**
     * Process the transport consultant request.
     *
     * @param array $params
     *
     * @return bool|Response
     */
    public function process(array $params)
    {
        if ($params['add-transport-consultant'] === 'Y') {
            return $this->saveTransportConsultant($params);
        }

        return $this->removeTransportConsultant($params);
    }

    /**
     * Save the transport consultant.
     *
     * @param array $params
     *
     * @return bool|Response
     */
    protected function saveTransportConsultant(array $params)
    {
        $addressResponse = $this->getBusinessServiceManager()->get('Lva\Address')->process(
            array(
                'data' => $params['address']
            )
        );

        $contactDetailsResponse = $this->getBusinessServiceManager()->get('Lva\ContactDetails')->process(
            array(
                'data' => array(
                    'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_CONSULTANT,
                    'writtenPermissionToEngage' => $params['writtenPermissionToEngage'],
                    'fao' => $params['transportConsultantName'],
                    'address' => $addressResponse->getData()['id']
                )
            )
        );

        $this->getBusinessServiceManager()
            ->get('Lva\PhoneContact')
            ->process(
                array(
                    'correspondenceId' => $contactDetailsResponse->getData()['id'],
                    'data' => array(
                        'contact' => $params['contact']
                    )
                )
            );

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);
        $response->setData(
            array(
                'id' => $contactDetailsResponse->getData()['id']
            )
        );

        return $response;
    }

    /**
     * Remove the transport consultant.
     *
     * @param array $params
     *
     * @return Response
     */
    protected function removeTransportConsultant(array $params)
    {
        unset($params);

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);
        $response->setData(
            array(
                'id' => null
            )
        );

        return $response;
    }
}
