<?php

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @todo update hardcoded stay types once data is available
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

class CaseStayController extends CaseController
{

    //temporary hardcoding of stay types until data available
    public $stayTypes = array(1 => 'Upper Tribunal', 2 => 'Traffic Commissioner / Transport Regulator');

    public function getPageHeading($action,$stayTypeId)
    {
        return $action . ' ' . $this->getStayTypes($stayTypeId);
    }

    public function getStayTypes($stayTypeId)
    {
        return $this->stayTypes[$stayTypeId];
    }

    /**
     * Show a table of stays for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        $results = $this->makeRestCall('Stay', 'GET', array('vosa_case' => $caseId));

        $variables = array('tab' => 'stays', 'stays' => $results);

        $casevariables = $this->getCaseVariables($caseId, $variables);

        $view = $this->getView($casevariables);

        $view->setTemplate('case/manage');

        return $view;
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
        $caseId = $this->fromRoute('case');
        $stayTypeId = $this->fromRoute('staytype');
        $pageData = $this->getCase($caseId);

        if (empty($pageData)) {
            return $this->notFoundAction();
        }

        $form = $this->generateFormWithData(
            'case-stay', 'processAddStay', array(
            'case' => $caseId,
            'stayType' => $stayTypeId
            )
        );

        //add in that this is an an action (reflected in the title)
        $stayTypeName = $this->getStayTypes($stayTypeId);
        $pageData['pageHeading'] = 'Add ' . $stayTypeName;
        $pageData['pageHeading'] = $this->getPageHeading('Add', $stayTypeId);

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add-stay');
        return $view;
    }

    /**
     * Loads the edit page
     *
     * @param array $data
     *
     * @todo Handle 404 and Bad Request
     * @todo Once user auth is ready, check user allowed access
     * @todo Once user auth is ready, add the user info to the data (fields are lastUpdatedBy and createdBy)
     * @todo Check to make sure the stay ID is really related to the case ID
     */
    public function editAction()
    {
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

        $pageData = array_merge($result, $case);

        $stayTypeId = $this->fromRoute('staytype');
        $stayTypeName = $this->getStayTypes($stayTypeId);
        $pageData['pageHeading'] = 'Edit ' . $stayTypeName;

        $form = $this->generateFormWithData(
            'case-stay', 'processEditStay', $result, true
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
     * @todo Once user auth is ready, add the user info to the data (fields are lastUpdatedBy and createdBy)
     * @todo Stay type (traffic commissioner / tribunal) needs implementing once data structure agreed
     * @todo Need to allow only one record for each stay type (can't do this yet as stay type structure not agreed)
     * @todo Need to deal with failures
     */
    public function processAddStay($data)
    {
        $data['lastUpdatedBy'] = 6;
        $data['createdBy'] = 7;
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'Stay');

        if (isset($result['id'])) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'case' => $data['case']));
        } else {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'add', 'case' => $data['case'], 'staytype' => $data['stayType']));
        }
    }

    /**
     * Process adding the stay
     *
     * @param array $data
     *
     * @todo Once user auth is ready, check user allowed access
     * @todo Once user auth is ready, add the user info to the data (field is lastUpdatedBy)
     * @todo Stay type (traffic commissioner / tribunal) needs implementing once data structure agreed
     * @todo Need to allow only one record for each stay type (can't do this yet as stay type structure not agreed)
     */
    public function processEditStay($data)
    {
        $data['lastUpdatedBy'] = 8;
        $data = array_merge($data, $data['fields']);

        $result = $this->processEdit($data, 'Stay');

        if (empty($result)) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'case' => $data['case']));
        } else {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'case' => $data['case'], 'staytype' => $data['stayType'], 'stay' => $data['stay']));
        }
    }
}
