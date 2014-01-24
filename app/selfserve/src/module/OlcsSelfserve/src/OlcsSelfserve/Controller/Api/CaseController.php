<?php
/**
 * Case Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Header\Location;

class CaseController extends AbstractRestfulController
{
    public function get($id)
    {
        $service = $this->getServiceLocator()->get('CaseServiceFactory');

        $data = $service->getCaseDataById(intval($id));

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
            'licenceId',
            'operatorId',
            'categories',
            'description',
            'ecms',
        ));

        $caseService = $this->getServiceLocator()->get('CaseServiceFactory');
        $caseId = $caseService->createCase($body);

        $location = new Location();
        $location->setUri($this->url()->fromRoute('api/case', array('id' => $caseId)));

        $this->response->setStatusCode(201);
        $this->response->getHeaders()->addHeader($location);

        return new JsonModel(array(
            'caseId' => $caseId,
        ));
    }

    public function summaryAction()
    {
        $caseId = intval($this->params('id'));

        if (!$this->request->isGet()) {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        } else if (!empty($caseId)) {
            $caseService = $this->getServiceLocator()->get('CaseServiceFactory');
            $data = $caseService->getCaseSummaryDetails($caseId);
        }
        if (empty($data)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        }

        return new JsonModel($data);
    }

    public function submissionsAction()
    {
        $caseId = intval($this->params('id'));
        $sortColumn = $this->params()->fromQuery('sortColumn', null);
        $sortReversed = $this->params()->fromQuery('sortReversed', false);

        if (empty($caseId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet()) {
            $submissionService = $this->getServiceLocator()->get('SubmissionServiceFactory');
            $data = array('rows' => $submissionService->getSubmissionsDataByCase($caseId, $sortColumn, $sortReversed ? 'dn' : null));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }

    public function convictionsAction()
    {
        $caseId = intval($this->params('id'));
        $sortColumn = $this->params()->fromQuery('sortColumn', null);
        $sortReversed = $this->params()->fromQuery('sortReversed', false);

        if (empty($caseId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet()) {
            $convictionService = $this->getServiceLocator()->get('ConvictionServiceFactory');
            $data = array('rows' => $convictionService->getConvictionDataByCase($caseId, $sortColumn, $sortReversed ? 'dn' : null));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }

    public function detailCommentAction()
    {
        $caseId = intval($this->params('id'));

        if (empty($caseId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet()) {
            $data = $this->getDetailComment($caseId);
        } else if ($this->request->isPut()) {
            $data = $this->updateDetailComment($caseId);
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }

    private function getDetailComment($caseId)
    {
        $service = $this->getServiceLocator()->get('CaseServiceFactory');
        $data = $service->getCaseDetailCommentByCaseId($caseId, true);

        if (empty($data)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        }

        return $data;
    }

    private function updateDetailComment($caseId)
    {
        $body = $this->processBodyContent($this->request);
        $service = $this->getServiceLocator()->get('CaseServiceFactory');

        //TODO: Validation and sanitation
        //TODO: Do version validation on edit and respond with a "409 Conflict" on a conflict
        //TODO: Check that the case ID is valid
        //TODO: Better error handling on unsuccessful processing

        $body = $this->pickValidKeys($body, array(
            'commentId',
            'caseDetailsNote',
            'detailTypeId',
        ));

        $body['caseId'] = $caseId;

        if (empty($body['commentId'])) {
            $result = $service->createCaseDetailComment($body);
        } else {
            $result = $service->updateCaseDetailComment((int)$body['commentId'], $body);
        }

        if (empty($result)) {
            $this->response->setStatusCode(500);
            $data = array(
                'error' => 'An unexpected error occured'
            );
        } else {
            // Whenever a PUT creates a new resource it should return HTTP code 201
            if (empty($body['commentId'])) {
                $this->response->setStatusCode(201);
            }
            $data = array(
                'commentId' => empty($body['commentId']) ? $result : $body['commentId'],
            );
        }

        return $data;
    }

    public function caseNotesAction()
    {
        $id = intval($this->params('id'));

        if (empty($id)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet() || $this->request->isPost()) {
            return $this->forward()->dispatch('Olcs\Api\CaseNote', array(
                'parentId' => $id,
                'parentType' => 'case',
            ));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }
}
