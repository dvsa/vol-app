<?php

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Stays
 */
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
     * Show a table of stays and appeals for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $addHref = array();


        $caseId = $this->fromRoute('case');

        if ((int) $caseId == 0) {
            return $this->notFoundAction();
        }

        $licenceId = $this->fromRoute('licence');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $config = $this->getServiceLocator()->get('Config');

        $staticStay = $config['static-list-data']['case_stay_outcome'];
        $staticAppealOutcomes = $config['static-list-data']['appeal_outcomes'];
        $staticAppealReasons = $config['static-list-data']['appeal_reasons'];

        $stayTypes = $this->getStayTypes();

        foreach (array_keys($stayTypes) as $id) {
            $addHref[$id] = $this->getUrl(
                'case_stay_action',
                [
                    'action' => 'add',
                    'licence' => $licenceId,
                    'case' => $caseId,
                    'stayType' => $id
                ]
            );
        }

        $addHref['appeal'] = $this->getUrl(
            'case_appeal',
            array(
                'action' => 'add',
                'licence' => $licenceId,
                'case' => $caseId,
            )
        );

        $stayRecords = $this->getStayData($licenceId, $caseId, $staticStay);
        $appealResult = $this->getAppealData($licenceId, $caseId, $staticAppealOutcomes, $staticAppealReasons);


        $variables = array(
            'tab' => 'stays',
            'addHref' => $addHref,
            'appealRecord' => $appealResult,
            'stayRecords' => $stayRecords,
            'stayTypes' => $stayTypes
        );

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
        $licenceId = $this->fromRoute('licence');
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
            return $this->redirectIndex($licenceId, $caseId);
        }

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_stay_action' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'case-stay',
            'processAddStay',
            array(
                'case' => $caseId,
                'stayType' => $stayTypeId,
                'licence' => $licenceId
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
     * @todo Check to make sure the stay ID is really related to the case ID
     */
    public function editAction()
    {
        $stayId = $this->fromRoute('stay');

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'licence' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $result = $this->makeRestCall('Stay', 'GET', array('id' => $stayId, 'bundle' => json_encode($bundle)));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $result['case'] = $result['case']['id'];
        $result['fields'] = $result;

        $case = $this->getCase($result['case']);

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

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $result['licence']),
                'case_stay_action' => array('licence' => $result['licence'], 'case' => $result['case'])
            )
        );

        $form = $this->generateFormWithData(
            'case-stay',
            'processEditStay',
            $result,
            true
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
     */
    public function processAddStay($data)
    {
        //if data already exists don't add more
        $existingRecord = $this->checkExistingStay($data['case'], $data['stayType']);

        if ($existingRecord) {
            return $this->redirectIndex($data['licence'], $data['case']);
        }

        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'Stay');

        if (isset($result['id'])) {
            return $this->redirectIndex($data['licence'], $data['case']);
        }

        return $this->redirect()->toRoute(
            'case_stay_action',
            array(
                'action' => 'add',
                'licence' => $data['licence'],
                'case' => $data['case'],
                'stayType' => $data['stayType']
            )
        );
    }

    /**
     * Process editing a stay
     *
     * @param array $data
     *
     * @todo Once user auth is ready, check user allowed access
     * @todo Once user auth is ready, add the user info to the data (fields are lastUpdatedBy and createdBy)
     */
    public function processEditStay($data)
    {
        $licence = $data['licence'];
        unset($data['licence']);

        $data = array_merge($data, $data['fields']);

        $result = $this->processEdit($data, 'Stay');

        if (empty($result)) {
            return $this->redirectIndex($licence, $data['case']);
        }

        return $this->redirectEditFail($licence, $data['case'], $data['stayType'], $data['stay']);
    }

    /**
     * Gets stay data for use on the index page
     *
     * @param int $licenceId
     * @param int $caseId
     * @param array static Static list data
     * @return array
     */
    private function getStayData($licenceId, $caseId, $static)
    {
        $stayRecords = array();

        $stayResult = $this->makeRestCall('Stay', 'GET', array('case' => $caseId));

        //need a better way to do this...
        foreach ($stayResult['Results'] as $stay) {
            if (isset($this->stayTypes[$stay['stayType']])) {
                $stay = $this->formatDates(
                    $stay,
                    array(
                        'requestDate'
                    )
                );

                $stayRecords[$stay['stayType']] = $stay;
                $stayRecords[$stay['stayType']]['outcome'] = $static[$stay['outcome']];
                $stayRecords[$stay['stayType']]['editHref'] = $this->getUrl(
                    'case_stay_action',
                    [
                        'action' => 'edit',
                        'licence' => $licenceId,
                        'stay' => $stay['id'],
                        'stayType' => $stay['stayType'],
                        'case' => $caseId
                    ]
                );

                $stayRecords[$stay['stayType']]['deleteHref'] = '';
            }
        }

        return $stayRecords;
    }

    /**
     * Retrieves appeal data
     *
     * @param int $licenceId
     * @param int $caseId
     * @param array $outcomes Static data for appeal outcomes
     * @param array $reasons Static data for appeal reasons
     * @return array
     */
    private function getAppealData($licenceId, $caseId, $outcomes, $reasons)
    {
        $appealResult = $this->makeRestCall('Appeal', 'GET', array('case' => $caseId));
        $appeal = array();

        if (!empty($appealResult['Results'][0])) {
            $appeal = $this->formatDates(
                $appealResult['Results'][0],
                array(
                    'deadlineDate',
                    'appealDate',
                    'hearingDate',
                    'decisionDate',
                    'papersDue',
                    'papersSent'
                )
            );

            $appeal['editHref'] = $this->getUrl(
                'case_appeal',
                array(
                    'action' => 'edit',
                    'licence' => $licenceId,
                    'case' => $caseId,
                    'appeal' => $appeal['id']
                )
            );

            $appeal['deleteHref'] = '';
            $appeal['outcome'] = $outcomes['appeal_outcome.'.$appeal['outcome']];
            $appeal['reason'] = $reasons['appeal_reason.'.$appeal['reason']];
        }

        return $appeal;
    }

    /**
     * Gets the url corresponding to the route and parameters
     *
     * @param string $route
     * @param array $params
     * @return type
     */
    private function getUrl($route, $params)
    {
        return $this->url()
                        ->fromRoute(
                            $route,
                            $params,
                            [

                            ],
                            true
                        );
    }

    /**
     * Redirect to the index page
     *
     * @param int $licence
     * @param int $case
     *
     * @return Response
     */
    private function redirectIndex($licence, $case)
    {
        return $this->redirect()->toRoute(
            'case_stay_action',
            array(
                'action' => 'index',
                'licence' => $licence,
                'case' => $case
            )
        );
    }

    /**
     * Redirect to the edit page on failure
     *
     * @param int $licence
     * @param int $case
     * @param int $stayType
     * @param int $stay
     *
     * @return Response
     */
    private function redirectEditFail($licence, $case, $stayType, $stay)
    {
        return $this->redirect()->toRoute(
            'case_stay_action',
            array(
                'action' => 'edit',
                'licence' => $licence,
                'case' => $case,
                'stayType' => $stayType,
                'stay' => $stay
            )
        );
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

    /**
     * Formats the specified fields in the supplied array with the correct date format
     * Expect to replace this with a view helper later
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function formatDates($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = date('d/m/Y', strtotime($data[$field]));
            }
        }

        return $data;
    }
}
