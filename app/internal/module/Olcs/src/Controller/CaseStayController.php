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

    private $stayTypes = array(1 => 'Upper Tribunal', 2 => 'Traffic Commissioner / Transport Regulator');

    /**
     * temporary hardcoding of stay types until proper data available
     *
     * @return array|boolean
     */
    private function getStayTypeName($stayTypeId)
    {
        if (isset($this->stayTypes[$stayTypeId])) {
            return $this->stayTypes[$stayTypeId];
        }

        return false;
    }

    /**
     * temporary hardcoding of stay types until proper data available
     *
     * @return array
     */
    private function getStayTypes()
    {
        return $this->stayTypes;
    }

    /**
     * Show a table of stays for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        if ((int) $caseId == 0) {
            return $this->notFoundAction();
        }

        $stayTypes = $this->getStayTypes();

        $result = $this->makeRestCall('Stay', 'GET', array('case' => $caseId));
        $records = array();

        //need a better way to do this...
        if (isset($result['Results'])) {
            foreach ($result['Results'] as $stay) {
                if (isset($stayTypes[$stay['stayType']])) {
                    $records[$stay['stayType']] = $stay;
                }
            }
        }

        $variables = array('tab' => 'stays', 'table' => 'test', 'records' => $records, 'stayTypes' => $stayTypes);
        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Add a new stay for a case
     *
     * @todo Handle 404 and Bad Request
     * @todo add message along with redirect if there's pre existing data
     * @return ViewModel
     */
    public function addAction()
    {
        $licence = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $pageData = $this->getCase($caseId);

        if (empty($pageData)) {
            return $this->notFoundAction();
        }

        $stayTypeId = $this->fromRoute('stayType');
        $stayTypeName = $this->getStayTypeName($stayTypeId);

        if (!$stayTypeName) {
            return $this->notFoundAction();
        }

        //if data already exists don't display the add form
        $existingRecord = $this->checkExistingStay($caseId, $stayTypeId);

        if ($existingRecord) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'licence' => $licence, 'case' => $caseId));
        }

        $form = $this->generateFormWithData(
            'case-stay', 'processAddStay', array(
            'case' => $caseId,
            'stayType' => $stayTypeId,
            'licence' => $licence
            )
        );

        //add in that this is an an action (reflected in the title)
        $pageData['pageHeading'] = 'Add ' . $stayTypeName;

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

        $result['licence'] = $this->fromRoute('licence');
        $pageData = array_merge($result, $case);

        $stayTypeId = $this->fromRoute('stayType');
        $stayTypeName = $this->getStayTypeName($stayTypeId);

        if (!$stayTypeName) {
            return $this->notFoundAction();
        }

        $form = $this->generateFormWithData(
            'case-stay', 'processEditStay', $result, true
        );

        //add in that this is an an action (reflected in the title)
        $pageData['pageHeading'] = 'Edit ' . $stayTypeName;

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
     */
    public function processAddStay($data)
    {
        //if data already exists don't add more
        $existingRecord = $this->checkExistingStay($data['case'], $data['stayType']);

        if ($existingRecord) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'licence' => $data['licence'], 'case' => $data['case']));
        }

        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'Stay');

        if (isset($result['id'])) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'licence' => $data['licence'], 'case' => $data['case']));
        }

        return $this->redirect()->toRoute('case_stay_action', array('action' => 'add', 'licence' => $data['licence'], 'case' => $data['case'], 'stayType' => $data['stayType']));
    }

    /**
     * Process adding the stay
     *
     * @param array $data
     *
     * @todo Once user auth is ready, check user allowed access
     * @todo Once user auth is ready, add the user info to the data (field is lastUpdatedBy)
     */
    public function processEditStay($data)
    {
        $licence = $data['licence'];
        unset($data['licence']);

        $data = array_merge($data, $data['fields']);

        $result = $this->processEdit($data, 'Stay');

        if (empty($result)) {
            return $this->redirect()->toRoute('case_stay_action', array('action' => 'index', 'licence' => $licence, 'case' => $data['case']));
        }

        return $this->redirect()->toRoute('case_stay_action', array('action' => 'edit', 'licence' => $licence, 'case' => $data['case'], 'stayType' => $data['stayType'], 'stay' => $data['stay']));
    }

    /**
     * Checks whether a stay already exists for the given case and staytype (only one should be allowed)
     *
     * @param int $caseId
     * @param int $stayTypeId
     *
     * @todo need to remove foreach stuff and make this just one rest call (as in commented out code)
     *
     * @return boolean
     */
    private function checkExistingStay($caseId, $stayTypeId)
    {
        $result = $this->makeRestCall('Stay', 'GET', array('case' => $caseId));

        if (isset($result['Results'])) {
            foreach ($result['Results'] as $stay) {
                if ($stay['stayType'] == $stayTypeId) {
                    return true;
                }
            }
        }

        /*
          $result = $this->makeRestCall('Stay', 'GET', array('stayType' => $stayTypeId, 'case' => $caseId));

          if(empty($result['results'])){
          return true;
          }
         */
        return false;
    }
}
