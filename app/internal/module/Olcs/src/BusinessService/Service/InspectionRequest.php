<?php

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequest implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $data = $params['data'];

        if ($params['type'] == 'application') {
            $data['application'] = $params['applicationId'];
        }
        $data['licence'] = $params['licenceId'];
        $data['requestorUser'] = $this->getServiceLocator()->get('Entity\User')->getCurrentUser()['id'];

        $responseData = [];

        $saved = $this->getServiceLocator()->get('Entity\InspectionRequest')->save($data);

        if (!empty($data['id'])) {
            $responseData['id'] = $data['id'];
        } else {
            $responseData['id'] = $saved['id'];
        }

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);
        $response->setData($responseData);

        return $response;
    }
}
