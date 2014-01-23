<?php
/**
 * Licence Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class LicenceController extends AbstractRestfulController
{
    public function get($licenceId)
    {
        $licenceService = $this->getServiceLocator()->get('licenceServiceFactory'); 

        $data = $licenceService->getLicenceData(intval($licenceId));

        if (empty($data)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        }

        return new JsonModel($data);
    }

    public function casesAction()
    {
        $licenceId = intval($this->params('id'));

        $sortColumn = $this->params()->fromQuery('sortColumn', null);
        $sortReversed = $this->params()->fromQuery('sortReversed', false);
        $offset = $this->params()->fromQuery('offset', null);
        $limit = $this->params()->fromQuery('limit', null);

        if (empty($licenceId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet()) {
            $caseService = $this->getServiceLocator()->get('CaseServiceFactory');
            $data = array_filter($caseService->getCasesDataByLicence(
                $licenceId,
                $sortColumn,
                $sortReversed == true,
                $limit,
                $offset
            ));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }

    public function transportManagersAction()
    {
        $licenceId = intval($this->params('id'));

        if (empty($licenceId)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else if ($this->request->isGet()) {
            $submissionService = $this->getServiceLocator()->get('SubmissionServiceFactory');
            $data = array('rows' => $submissionService->getTransportManagersDataByLicence($licenceId));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
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
                'parentType' => 'licence',
            ));
        } else {
            $this->response->setStatusCode(405);
            $data = array('error' => 'Method not allowed');
        }

        return new JsonModel($data);
    }
}
