<?php

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Penalties
 */
class CasePenaltyController extends CaseController
{

    /**
     * Show a table of stays and appeals for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        if ((int) $caseId == 0) {
            return $this->notFoundAction();
        }

        $licenceId = $this->fromRoute('licence');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $variables = array(
            'tab' => 'penalties',
            'penaltyRecord' => $penaltyResult
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Add a new stay for a case
     *
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
        $existingRecord = $this->checkExistingPenalty($caseId);

        if ($existingRecord) {
            return $this->redirectIndex($licenceId, $caseId);
        }

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_penalty' => array('licence' => $licenceId, 'case' => $caseId)
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
        $pageData['pageHeading'] = 'Add ' . $stayTypeName . ' Stay';

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add-stay');
        return $view;
    }

    /**
     * Loads the edit page
     *
     * @param array $data
     *
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
        $pageData['pageHeading'] = 'Edit ' . $stayTypeName . ' Stay';

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add-stay');
        return $view;
    }

    /**
     * Retrieves penalty data
     *
     * @param int $caseId
     * @return array
     */
    private function getPenaltyData($caseId)
    {
        $penaltyResult = $this->makeRestCall('Penalty', 'GET', array('case' => $caseId));

        return $penaltyResult;
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
            'case_penalty',
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
     *
     * @return Response
     */
    private function redirectEditFail($licence, $case)
    {
        return $this->redirect()->toRoute(
            'case_penalty',
            array(
                'action' => 'edit',
                'licence' => $licence,
                'case' => $case,
            )
        );
    }

    /**
     * Checks whether a penalty already exists for the given case
     *
     * @param int $caseId
     * @param int $stayTypeId
     *
     * @todo need to remove foreach stuff and make this just one rest call (as in commented out code)
     *
     * @return boolean
     */
    private function checkExistingStay($caseId)
    {
        $result = $this->makeRestCall('Penalty', 'GET', array('case' => $caseId));

        return $result['Count'];
    }
}
