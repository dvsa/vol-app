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
                'case_manage' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );
        
        $submission = $this->createSubmission($routeParams);
        $data = array(
            'createdBy' => 1,
            'text' => $submission,
            'vosaCase' => $routeParams['case'],
        );
        //$result = $this->processAdd($data, 'Submission');
        $submission = json_decode($submission, true);
        $submission = $this->createSubmissionViewData($submission, $routeParams);
        $view = new ViewModel(
            array(
                'headScript' => array('/static/js/conviction.js'),
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
    
    public function createSubmissionViewData($submission, $routeParams)
    {
        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $routeParams['licence']));
//print_r($licenceData);
        $exclude = array('transport-managers' => array('attr' => 'licenceType', 'val' => 'Standard National'));
        foreach($submission as $key => $section) {
            if (isset($exclude[$key]) && $exclude[$key]['val'] != $licenceData[$exclude[$key]['attr']]) {
                print 'excluded';
                //unset($submission[$key]);
            }
        }
        return $submission;
    }
    
    public function createSubmission($routeParams)
    {
        $caseData = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        $submission = array();
        $submission['case-details'] = $caseData;
        $submission['persons'] = array();
        $submission['transport-managers'] = array();
        $submission['outstanding-application'] = array();
        $submission['environmental'] = array();
        $submission['previous-history'] = array();
        $submission['operating-centre'] = array();
        $submission['conditions-undertakings'] = array();
        $submission['prohibition-history'] = array();
        $submission['conviction-history'] = array();
        $submission['bus-services-registered'] = array();
        $submission['current-submission'] = array();
        //$submission['recommendation-decision'] = array();
        $jsonSubmission = json_encode($submission);
        return $jsonSubmission;
    }

    public function editAction()
    {
        
    }

}
