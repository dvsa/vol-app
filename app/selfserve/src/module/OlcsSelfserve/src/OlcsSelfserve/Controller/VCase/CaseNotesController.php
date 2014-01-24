<?php
namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Description of CaseNotesController
 *
 * @author valtechuk
 */
class CaseNotesController extends AbstractActionController
{
    public function indexAction() {
    }
    
    public function licenceAddAction()
    {
        return $this->processAddPage('Olcs\Licence', $this->getRequest()->getQuery('licenceId'));
    }

    public function caseReadAction()
    {
        return $this->processReadPage('Olcs\Case', $this->getRequest()->getQuery('caseId'));
    }
    
    public function licenceReadAction()
    {
        return $this->processReadPage('Olcs\Licence', $this->getRequest()->getQuery('licenceId'));
    }

    public function caseAddAction()
    {
        return $this->processAddPage('Olcs\Case', $this->getRequest()->getQuery('caseId'));
    }

    private function processReadPage($service, $id)
    {
        $id = (int)$id;

        if (empty($id)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $caseNotes =  $this->service($service)->get($id . '/case-notes')['rows'];

        $view = new ViewModel(array('listData' => $caseNotes));
        $view->setTemplate('olcs/vcase/list/caseNotesList');

        return $view;
    }

    private function processAddPage($service, $id)
    {
        $id = (int)$id;

        if (empty($id)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $params = array(
            'priority' => $this->getRequest()->getQuery('priority'),
            'comment' => $this->getRequest()->getQuery('comment'),
        );

        return $this->service($service)->create($id . '/case-notes', $params)['caseNoteId'];
    }
}
