<?php
/**
 * Case Note Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Header\Location;

class CaseNoteController extends AbstractRestfulController
{
    public function getList()
    {
        $service = $this->getServiceLocator()->get('CaseNoteServiceFactory');

        $id = (int)$this->params('parentId');
        $type = $this->params('parentType');

        //TODO: Verify that an entity with $id actually exists, return a 404 otherwise

        if (empty($id) || !in_array($type, array('case', 'licence'))) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        } else {
            $data = $service->getCaseNotesData($id, $type);
        }

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
            'priority',
            'comment',
        ));

        $id = (int)$this->params('parentId');
        $type = $this->params('parentType');

        $service = $this->getServiceLocator()->get('CaseNoteServiceFactory');
        $caseNoteId = $service->createCaseNote($id, $type, $body);

        // $location = new Location();
        // $location->setUri($this->url()->fromRoute('api/case-note', array('id' => $caseNoteId)));

        $this->response->setStatusCode(201);
        // $this->response->getHeaders()->addHeader($location);

        return new JsonModel(array(
            'caseNoteId' => $caseNoteId,
        ));
    }
}
