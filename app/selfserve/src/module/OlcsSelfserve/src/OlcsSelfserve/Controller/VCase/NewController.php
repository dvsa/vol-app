<?php

/**
 * Creates cases.
 *
 * OLCS-5
 *
 * @package		olcs
 * @subpackage	vcase
 * @author		Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;       // <-- Add this import

class NewController extends AbstractActionController
{

    public $messages = null;

    public function indexAction() 
    {
        $this->getRequest()->getQuery()->set('licenceId', $this->getEvent()->getRouteMatch()->getParam('licenceId'));
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'list of cases');
        $page->setParams(array('licenceId' =>  $this->getEvent()->getRouteMatch()->getParam('licenceId')));
        
        $vcaseNewForm = new Form\VCase\NewForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $validator = new Form\FormEmptyValidator();
            $valueFound = $validator->isValid($request->getPost());
			if ($valueFound) {
				$caseId = $this->createCase($request->getPost()->toArray());
                                        $licenceId = $request->getQuery('licenceId');
                                        return $this->redirect()->toUrl('/case/'.$licenceId.'/'.$caseId.'/dashboard?');
			} else {
				$this->messages = array('You must enter a value in at least one field');
			}
        }
          
        $params = $this->getPageParams($request->getQuery('licenceId'));
        $this->setFormValues($vcaseNewForm, $params);
        $view = new ViewModel(array('vcaseNewForm' => $vcaseNewForm,
            'operatorName' => $params['operator']['operatorName'],
            'licenceNumber' => $params['licenceNumber'],
            'licenceId' => $params['licenceId'],
            'messages' => $this->messages));
        $view->setTemplate('olcs/vcase/new');

        return $view;
        
    }
    
    private function setFormValues($form, $values) 
    {
        
        $form->setAttribute('action', '/case/new/'.$this->getEvent()->getRouteMatch()->getParam('licenceId'));
        $operatorId = $values['operator']['operatorId'];
        $op = $form->get("operatorId");
        $op->setValue($operatorId);

        $licenceId = $values['licenceId'];
        $lic = $form->get("licenceId");
        $lic->setValue($licenceId);
		
        $licenceNumber = $values['licenceNumber'];
        $lic = $form->get("licenceNumber");
        $lic->setValue($licenceNumber);

        $operatorName = $values['operator']['operatorName'];
        $opName = $form->get("operatorName");
        $opName->setValue($operatorName);
        
    }
    
    private function getPageParams($licenceId)
    {
        $licence = $this->service('Olcs\Licence')->get(intval($licenceId));

        if (empty($licence)) {
            $this->getResponse()->setStatusCode(404);
            return false;
        }

        return $licence;
    }
    
    public function doneAction() {
        
        $view = new ViewModel(array('operatorName' => $this->getRequest()->getQuery('operatorName'),
									'caseNumber' => $this->getRequest()->getQuery('caseNumber'),
									'licenceNumber' => $this->getRequest()->getQuery('licenceNumber')));
        $view->setTemplate('olcs/vcase/done');
        return $view;
        
    }
    
    private function createCase(array $params)
    {
        return $this->service('Olcs\Case')->create($params)['caseId'];
    }

}
