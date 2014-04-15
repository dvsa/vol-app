<?php

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseStayController extends CaseController
{
    public function indexAction()
    {
        
    }
    
    /**
     * Add a new stay for a case
     *
     * @todo Handle 404 and Bad Request
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $caseId = $this->params()->fromRoute('case');
        $pageData = $this->getCase($caseId);

        if (empty($pageData)) {
            return $this->notFoundAction();
        }

        $form = $this->generateFormWithData(
            'case-stay', 'processAddStay', array(
            'case' => $caseId
            )
        );
        
        //add in that this is an an action (reflected in the title)
        $pageData['pageHeading'] = 'Add';

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add-stay');
        return $view;
    }
    
    public function editAction(){
        $caseId = $this->params()->fromRoute('case');
        $stayId = $this->params()->fromRoute('stay');

        $result = $this->makeRestCall('Stay', 'GET', array('id' => $stayId));
        $result['fields'] = $result;
        
        $case = $this->getCase($caseId);
        
        $pageData = array_merge($result,$case);
        $pageData['pageHeading'] = 'Edit';
        
        $form = $this->generateFormWithData(
            'case-stay', 'processEditStay', $result
        );

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add-stay');
        return $view;
    }
    
    /**
     * Process adding the stay
     *
     * @param array $data
     */
    protected function processAddStay($data)
    {
        //$data['created_on'] = date('Y-m-d');
        //$data['last_updated_on'] = date('Y-m-d');
        $data['stayType'] = "1";
        $data['lastUpdatedBy'] = 6;
        $data['createdBy'] = 7;
        $data = array_merge($data, $data['fields']);
        //print_r($data);
        //die();
        
        $result = $this->processAdd($data, 'Stay');
        
        //print_r($result);
        //die();
        
        if (isset($result['id'])) {
            $this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'case' => $data['case'], 'stay' => $result['id']));
        }
    }
    
    /**
     * Process adding the stay
     *
     * @param array $data
     */
    protected function processEditStay($data)
    {
        $data['stayType'] = "1";
        $data['lastUpdatedBy'] = 8;
        $data = array_merge($data, $data['fields']);
        //print_r($data);
        //die();
        
        $result = $this->processEdit($data, 'Stay');
        
        if (isset($result['id'])) {
            $this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'case' => $data['case'], 'stay' => $result['id']));
        }
    }
}
