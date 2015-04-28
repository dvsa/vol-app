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
use Common\Service\Entity\InspectionRequestEntityService;
use Olcs\View\Model\Email\InspectionRequest as InspectionRequestEmailViewModel;

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
        if ($params['type'] !== 'applicationFromGrant') {
            $data = $params['data'];
        } else {
            $post = $params['data'];
            $today = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
            $requestedDate = $today->format('Y-m-d');
            $dueDate = $today
                ->add(new \DateInterval('P' . $post['inspection-request-grant-details']['dueDate'] . 'M'))
                ->format('Y-m-d');
            $ocService = $this->getServiceLocator()->get('Olcs\Service\Data\OperatingCentresForInspectionRequest');
            $ocService->setType('application');
            $ocService->setIdentifier($params['applicationId']);
            $ocs = $ocService->fetchListOptions('');
            $operatingCentreId = array_keys($ocs)[0];
            $data = [
                'requestType' => InspectionRequestEntityService::REQUEST_TYPE_NEW_OP,
                'requestDate' => $requestedDate,
                'dueDate' => $dueDate,
                'resultType' => InspectionRequestEntityService::RESULT_TYPE_NEW,
                'requestorNotes' =>  $post['inspection-request-grant-details']['caseworkerNotes'],
                'reportType' => InspectionRequestEntityService::REPORT_TYPE_MAINTANANCE_REQUEST,
                'operatingCentre' => $operatingCentreId
            ];
        }

        if ($params['type'] === 'application' || $params['type'] === 'applicationFromGrant') {
            $data['application'] = $params['applicationId'];
        }
        $data['licence'] = isset($params['licenceId']) ?
            $params['licenceId'] :
            $this->getServiceLocator()
                ->get('Entity\Application')
                ->getLicenceIdForApplication($params['applicationId']);

        $data['requestorUser'] = $this->getServiceLocator()->get('Entity\User')->getCurrentUser()['id'];

        $responseData = [];

        $saved = $this->getServiceLocator()->get('Entity\InspectionRequest')->save($data);

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);

        if (!empty($data['id'])) {
            // update
            $responseData['id'] = $data['id'];
        } else {
            // create
            $responseData['id'] = $saved['id'];

            $emailSent = false;
            $emailService = $this->getServiceLocator()->get('Email\InspectionRequest');

            try {
                $view = new InspectionRequestEmailViewModel();
                $emailSent = $emailService->sendInspectionRequestEmail($view, $responseData['id']);
            } catch (\Exception $e) {
                $this->getServiceLocator()->get('Zend\Log')
                    ->err("Failed to send inspection request email: " . $e->getMessage());
            }

            // @NOTE commenting it until the email environment not ready
            // this change is agreed with Steve to test and complete OLCS-8242
            // if (!$emailSent) {
            if (false) {
                // AC specify not to save the inspection request record if email
                // cannot be sent. However, we have already had to save the
                // record to attempt to send the email, so just delete it here
                // and return a failure response
                $this->getServiceLocator()->get('Entity\InspectionRequest')
                    ->delete($saved['id']);
                $response->setType(Response::TYPE_FAILED);
                return $response;
            }
        }

        $response->setData($responseData);

        return $response;
    }
}
