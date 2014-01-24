<?php
/**
 * Application Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Mike Cooper
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Header\Location;
use Doctrine\ORM\OptimisticLockException;

class ApplicationController extends AbstractRestfulController
{
    public function getList()
    {
        $applicationService = $this->getServiceLocator()->get('ApplicationServiceFactory'); 

        $data = array(
            'rows' => $applicationService->getApplicationData()
        );

        return new JsonModel($data);
    }
    
    public function get($id)
    {
        $applicationService = $this->getServiceLocator()->get('ApplicationServiceFactory'); 
        $data = $applicationService->get($id);
        if (!$data) {
            return new JsonModel(array('error' => AbstractRestfulController::ERROR_NOT_FOUND));
        }
        return new JsonModel($data);
    }
    
    public function feesAction()
    {
        $applicationService = $this->getServiceLocator()->get('ApplicationServiceFactory'); 
        if ($appId = $this->getEvent()->getRouteMatch()->getParam('appId')) {
            $data = $applicationService->get($appId);
            if (!$data) {
                return new JsonModel(array('error' => AbstractRestfulController::ERROR_NOT_FOUND));
            }
            $data = isset($data['applicationFees']) ? $data['applicationFees'] : array();
        } else {
            return new JsonModel(array('error' => AbstractRestfulController::ERROR_MISSING_PARAMETERS));
        }
        return new JsonModel($data);
    }
    
    public function create($body)
    {
        if (empty($body)) {
            $this->response->setStatusCode(400);
            return new JsonModel(array(
                'error' => 'Invalid request body'
            ));
        }

        $data = $this->prepareCreationData($body);

        // Do the database work

        try {
            $applicationId = $this->transactional(function () use ($data) {
                $serviceLocator = $this->getServiceLocator();

                $addressService = $serviceLocator->get('AddressServiceFactory');
                $organisationService = $serviceLocator->get('OrganisationServiceFactory');
                $licenceService = $serviceLocator->get('LicenceServiceFactory');
                $applicationService = $serviceLocator->get('ApplicationServiceFactory');

                if (empty($data['operator']['operatorId'])) {
                    $operatorId = $organisationService->createOrganisation($data['operator']);

                    $data['licence']['operator'] = $operatorId;
                    $data['operator']['operatorId'] = $operatorId;
                    $data['registeredAddress']['operator'] = $operatorId;

                    $addressService->createContactDetails($data['registeredAddress']);
                } else {
                    $organisationService->updateOrganisationByData($data['operator']);
                    $addressService->updateContactDetailsByData($data['registeredAddress']);
                }

                $licenceId = $licenceService->createLicence($data['licence']);
                $data['application']['licence'] = $licenceId;

                $applicationId = $applicationService->createApplication($data['application']);

                return $applicationId;
            });

            $location = new Location();
            $location->setUri($this->url()->fromRoute('api/application', array('id' => $applicationId)));

            $this->response->setStatusCode(201);
            $this->response->getHeaders()->addHeader($location);

            $result = array(
                'applicationId' => $applicationId,
            );
        } catch (OptimisticLockException $e) {
            $this->response->setStatusCode(409);
            $result = array(
                'error' => self::ERROR_CONFLICT,
                'error_message' => 'There is a conflicting version number in the request',
            );
        }

        return new JsonModel($result);
    }

    /**
     * Separates and simplifies the data of the creation body
     *
     * Purpose is to make it easier to use the data supplied when creating and
     * updating entities while still conforming to the RESTful component design
     *
     * @param  array $body The request body
     * @return array       The prepared data
     */
    private function prepareCreationData(array $body)
    {
        $data = array();

        // Split up the data across a couple of different parameters and pick valid keys

        $body = $this->pickValidKeys($body, array(
            'receivedAt',
            'licence',
        ));

        $data['licence'] = $this->pickValidKeys(empty($body['licence']) ? array() : $body['licence'], array(
            'licenceType',
            'tradeType',
            'goodsOrPsv',
            'trafficArea',
            'tradingNames',
            'operator',
        ));

        $data['operator'] = empty($data['licence']['operator']) ? array() : $data['licence']['operator'];
        $data['operator'] = $this->pickValidKeys($data['operator'], array(
            'version',
            'operatorId',
            'operatorName',
            'entityType',
            'owners',
            'registeredAddress',
            'registeredCompanyNumber',
        ));

        $data['registeredAddress'] = empty($data['operator']['registeredAddress']) ? array() : $data['operator']['registeredAddress'];
        $data['registeredAddress'] = $this->pickValidKeys($data['registeredAddress'], array(
            'version',
            'line1',
            'line2',
            'line3',
            'line4',
            'town',
            'country',
            'postcode',
        ));

        unset($body['licence'], $data['operator']['registeredAddress']);

        $data['application'] = $body;

        // Simplify and extend the data

        if (empty($data['licence']['operator']['operatorId'])) {
            $data['licence']['operator'] = null;
        } else {
            $data['licence']['operator'] = $data['licence']['operator']['operatorId'];
        }

        $data['registeredAddress'] = array(
            'contactDetailsType' => 'Registered',
            'operator' => $data['licence']['operator'],
            'address' => $data['registeredAddress'],
        );

        $data['application']['trafficArea'] = $data['licence']['trafficArea'];

        return $data;
    }
    
    public function getTempDetailsAction()
    {
        
        $applicationService = $this->getServiceLocator()->get('ApplicationServiceFactory');
        $id = $this->getEvent()->getRouteMatch()->getParam('appId');
        $data = $applicationService->getApplicationDetails($id);
        if (!$data) {
            return new JsonModel(array('error' => AbstractRestfulController::ERROR_NOT_FOUND));
        }
        $data = array('appdata' => $data);
        return new JsonModel($data);
    }
    
}
