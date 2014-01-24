<?php
/**
 * Submission Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Header\Location;

class SubmissionController extends AbstractRestfulController
{
    public function get($id)
    {
        $service = $this->getServiceLocator()->get('SubmissionServiceFactory');

        $data = $service->getSubmissionDataById(intval($id));

        if (empty($data)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
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

        //TODO: Validation and sanitation

        $body = $this->pickValidKeys($body, array(
            'caseId',
            'createdAt',
            'type',
            'text',
        ));

        $submissionService = $this->getServiceLocator()->get('SubmissionServiceFactory');
        $submissionId = $submissionService->createSubmission($body);

        $location = new Location();
        $location->setUri($this->url()->fromRoute('api/submission', array('id' => $submissionId)));

        $this->response->setStatusCode(201);
        $this->response->getHeaders()->addHeader($location);

        return new JsonModel(array(
            'submissionId' => $submissionId,
        ));
    }

    public function patch($id, $body)
    {
        $version = $this->getVersion();

        if (empty($body) || empty($version)) {
            $this->response->setStatusCode(400);
            return new JsonModel(array(
                'error' => 'Invalid request parameters'
            ));
        }

        //TODO: Validation and sanitation
        //TODO: Respond with a "409 Conflict" on a version conflict

        $body = $this->pickValidKeys($body, array(
            'text',
        ));

        $submissionService = $this->getServiceLocator()->get('SubmissionServiceFactory');
        $version = $submissionService->updateSubmission($id, $version, $body);

        if (empty($version)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else {
            $data = array('version' => $version);
        }

        return new JsonModel($data);
    }

    public function decisionAction()
    {
        return $this->recommendationAction(true);
    }

    public function recommendationAction($decision = false)
    {
        $submissionId = intval($this->params('id'));

        if (empty($submissionId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isPost()) {
            $data = $this->createDecisionRecommendation($submissionId, $decision);
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }

    private function createDecisionRecommendation($submissionId, $decision)
    {
        $body = $this->processBodyContent($this->request);
        $service = $this->getServiceLocator()->get('SubmissionServiceFactory');

        //TODO: Validation and sanitation
        //TODO: Check that the submission ID, senderUserId and recipientUserId are valid

        $body = $this->pickValidKeys($body, array(
            'senderUserId',
            'recipientUserId',
            'type',
            'comment',
            'otherText',
            'urgent',
            'senderRole',
            'senderLocation',
        ));

        $body['submissionId'] = $submissionId;

        if ($decision) {
            $result = $service->createSubmissionDecision($body);
        } else {
            $result = $service->createSubmissionRecommendation($body);
        }

        if (empty($result)) {
            $this->response->setStatusCode(500);
            $data = array('error' => 'An unexpected error occured');
        } else {
            $this->response->setStatusCode(201);
            $data = array('id' => $result);
        }

        return $data;
    }
}
