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
    
    /**
     * Add a new stay for a case
     *
     * @todo Handle 404 and Bad Request
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $caseId = $this->fromRoute('case');
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
    
    /**
     * Loads the edit page
     *
     * @param array $data
     * 
     * @todo Once user auth is ready, check user allowed access
     * @todo Check to make sure the stay ID is really related to the case ID
     */
    public function editAction(){
        $stayId = $this->fromRoute('stay');

        $result = $this->makeRestCall('Stay', 'GET', array('id' => $stayId));
        
        if (empty($result)) {
            return $this->notFoundAction();
        }
        
        $result['fields'] = $result;
        
        $caseId = $this->fromRoute('case');
        $case = $this->getCase($caseId);
        
        if (empty($case)) {
            return $this->notFoundAction();
        }
        
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
     * 
     * @todo Once user auth is ready, check user allowed access
     * @todo Implement redirect to main stay page once it is ready
     */
    public function processAddStay($data)
    {
        $data['stayType'] = "1";
        $data['lastUpdatedBy'] = 6;
        $data['createdBy'] = 7;
        $data = array_merge($data, $data['fields']);
        
        $result = $this->processAdd($data, 'Stay');
        
        if (isset($result['id'])) {
            //$this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'case' => $data['case'], 'stay' => $result['id']));
        }
    }
    
    /**
     * Process adding the stay
     *
     * @param array $data
     * 
     * @todo Once user auth is ready, check user allowed access
     * @todo Implement redirect to main stay page once it is ready
     */
    public function processEditStay($data)
    {
        $data['stayType'] = "1";
        $data['lastUpdatedBy'] = 8;
        $data = array_merge($data, $data['fields']);
        
        $result = $this->processEdit($data, 'Stay');
        
        if (isset($result['id'])) {
            //$this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'case' => $data['case'], 'stay' => $result['id']));
        }
    }
}
