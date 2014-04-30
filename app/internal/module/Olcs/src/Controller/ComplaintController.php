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
        var_dump($_POST);exit;

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

        $bundle = array(
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
            $data['complaint-details']['value'] = '';
            // set up data
            //$data = $this->getNewComplaintData();
            // add contact details
            $result = $this->processAdd($data['driver-details'], 'ContactDetails');
            var_dump($result);exit;

            // add driver to person table
            $result = $this->processAdd($data['driver-details'], 'Driver');
            var_dump($result);exit;
            // add complainant to person table

            // add contact details for both persons

            // add contact details to driver table

            // add complaint

            // add link to complaint_case table

            $newComplainantData = [
                'version' => 1,
                'organisation_id' => 7,
                'contact_details_type' => 'Complainant',
                'is_deleted' => 0,
                'person' => $data['complainant-details'],
                ];
            $result = $this->processAdd($newComplainantData, 'ContactDetails');
            //$result = $this->processAdd($data['complaint-details'], 'Complaint');
            //$result = $this->processAdd($data['complainant-details'], 'Person');
            //$result = $this->processAdd($data['driver-details'], 'Person');

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
}
