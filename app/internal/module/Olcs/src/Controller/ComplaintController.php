<?php

/**
 * Complaint controller
 *
 * Adds/edits a complaint
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Complaint controller
 *
 * Adds/edits a complaint
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ComplaintController extends FormActionController
{

    /**
     * Add form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('complaint', 'case', 'licence', 'id'));

        if (null !== $this->params()->fromPost('cancel-complaint')) {
            return $this->redirect()->toRoute('case_complaints', array('complaint' => $routeParams['complaint']));
        }
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_complaints' => array('case' => $routeParams['case'])
            )
        );

        $data = array('vosaCase' => $routeParams['case']);

        // todo hardcoded organisation id for now
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));

        // todo hardcoded organisation id for now
        $data['organisation-details']['id'] = 7;
        $data['organisation-details']['version'] = 1;

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateForm(
            'complaint', 'processComplaint'
        );

        $form->setData($data);
        //$form->setMessages(array('blah' => 'This is a test message'));
        $view = new ViewModel(
            array(
            'form' => $form,
            'headScript' => array('/static/js/complaint.js'),
            'params' => array(
                'pageTitle' => 'add-complaint',
                'pageSubTitle' => 'subtitle-complaint-text'
            )
            )
        );
        $view->setTemplate('complaint/form');
        return $view;
    }

    public function editAction()
    {
        $routeParams = $this->getParams(
            array(
                'case',
                'licence',
                'id',
            )
        );
        if (null !== $this->params()->fromPost('cancel-complaint')) {
            return $this->redirect()->toRoute('case_complaints', array('complaint' => $routeParams['complaint']));
        }
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_complaints' => array('licence' => $routeParams['licence'], 'case' => $routeParams['case'])
            )
        );

        $bundle = $this->getComplaintBundle();

        $data = $this->makeRestCall('Complaint', 'GET', array('id' => $routeParams['id'], 'bundle' => json_encode($bundle)));
        if (isset($data['id'])) {
            $data['vosaCase'] = $data['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $data['organisation-details'] = $data['organisation'];
        $data['complaint-details'] = $data;
        $data['complainant-details'] = $data['complainant']['person'];
        $data['driver-details'] = $data['driver']['contactDetails']['person'];

        $form = $this->generateFormWithData(
            'complaint', 'processComplaint', $data, true
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'headScript' => array(
                    '/static/js/complaint.js'
                ),
                'params' => array(
                    'pageTitle' => 'edit-complaint',
                    'pageSubTitle' => 'subtitle-complaint-text'
                )
            )
        );
        $view->setTemplate('complaint/form');
        return $view;
    }

    public function processComplaint($data)
    {
        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit') {
            $result = $this->processEdit($data['complaint-details'], 'Complaint');
            $result = $this->processEdit($data['complainant-details'], 'Person');
            $result = $this->processEdit($data['driver-details'], 'Person');
        } else {
            // configure complaint data

            $newData = $data['complaint-details'];
            $newData['vosaCases'][] = $data['vosaCase'];
            $newData['value'] = '';
            $newData['vehicle_id'] = 1;
            $newData['organisation'] = $data['organisation-details'];

            $newData['driver']['contactDetails']['contactDetailsType'] = 'Driver';
            $newData['driver']['contactDetails']['is_deleted'] = 0;
            $newData['driver']['contactDetails']['version'] = 1;
            $newData['driver']['contactDetails']['person'] = $data['driver-details'];

            $newData['complainant']['contactDetailsType'] = 'Complainant';
            $newData['complainant']['is_deleted'] = 0;
            $newData['complainant']['version'] = 1;
            $newData['complainant']['person'] = $data['complainant-details'];

            $result = $this->processAdd($newData, 'Complaint');

        }

        return $this->redirect()->toRoute(
            'case_complaints',
            array(
                'case' => $routeParams['case'], 'licence' => $routeParams['licence']
            )
        );
    }
    /**
     * Method to map the data in the correct format
     */
    public function getNewComplaintData()
    {

    }

    /**
     * Method to return the bundle required for getting complaints and related
     * entities from the database.
     *
     * @return array
     */
    private function getComplaintBundle()
    {
        return array(
           'complaint' => array(
               'properties' => array('ALL'),
           ),
            'children' => array(
                'driver' => array(
                    'properties' => array('id', 'version'),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array('id', 'version'),
                            'children' => array(
                                'person' => array(
                                    'properties' => array(
                                        'id',
                                        'version',
                                        'firstName',
                                        'middleName',
                                        'surname',
                                    )
                                )
                            )
                        )
                    )
                ),
                'complainant' => array(
                   'properties' => array('person'),
                   'children' => array(
                       'person' => array(
                           'properties' => array(
                               'id',
                               'version',
                               'firstName',
                               'middleName',
                               'surname',
                           )
                       )
                   )
                ),
                'organisation' => array(
                   'properties' => array('id', 'version', 'name'),
                )
            )
        );
    }
}
