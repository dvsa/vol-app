<?php
/**
 * Submission controller
 * Create, view and modify submissions
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class SubmissionController extends FormActionController
{

    /**
     * Add submission action
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id'));
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_manage' => array(
                    'case' => $routeParams['case'],
                    'licence' => $routeParams['licence'],
                    'tab' => 'overview'
                )
            )
        );
        
        $submission = $this->createSubmission($routeParams);
        $data = array(
            'createdBy' => 1,
            'text' => $submission,
            'vosaCase' => $routeParams['case'],
        );
        
        $result = $this->processAdd($data, 'Submission');
        
        $submission = json_decode($submission, true);
        $view = $this->getViewModel(
            array(
                'params' => array(
                    'pageTitle' => 'case-submission',
                    'pageSubTitle' => 'case-submission-text',
                    'data' => $submission
                )
            )
        );
        $view->setTemplate('submission/page');
        return $view;
    }
    
    /**
     * Return json encoded submission
     * @param type $routeParams
     * @return type
     */
    public function createSubmission($routeParams)
    {
        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $routeParams['licence']));
        $caseData = array();
        //$caseData = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        $submission = array();
        $submission['case-summary-info'] = $caseData;
        $submission['persons'] = array();
        if (in_array(strtolower($licenceData['licenceType']), array('standard national', 'standard international'))) {
            $submission['transport-managers'] = array();
        }
        $submission['outstanding-applications'] = array();
        $submission['environmental'] = array();
        $submission['objections'] = array();
        $submission['representations'] = array();
        $submission['previous-history'] = array();
        $submission['operating-centre'] = array();
        $submission['conditions'] = array();
        $submission['undertakings'] = array();
        $submission['annual-test-history'] = array();
        $submission['prohibition-history'] = array();
        $submission['conviction-history'] = array();
        if (strtolower($licenceData['goodsOrPsv']) == 'psv') {
            $submission['bus-services-registered'] = array();
            $submission['bus-compliance-issues'] = array();
        }
        $submission['current-submission'] = array();
        //$submission['recommendation-decision'] = array();
        $jsonSubmission = json_encode($submission);
        return $jsonSubmission;
    }
}
