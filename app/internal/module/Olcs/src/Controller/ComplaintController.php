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
        $routeParams = $this->getParams(array('complaint', 'licence', 'id'));

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
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));

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
                'pageSubTitle' => 'add-complaint-text'
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
                'case_complaints' => array('case' => $routeParams['case'])
            )
        );

        $bundle = array(
            'children' => array(
                'vosaCase' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $data = $this->makeRestCall('Complaint', 'GET', array('id' => $routeParams['id']), $bundle);
        if (isset($data['vosaCase'])) {
            $data['vosaCase'] = $data['vosaCase']['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $data['id'] = $routeParams['id'];
        $data['complainant-details'] = $data;
        $data['driver-details'] = $data;

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
                    'pageTitle' => 'add-complaint',
                    'pageSubTitle' => 'add-complaint-text'
                )
            )
        );
        $view->setTemplate('complaint/form');
        return $view;
    }
/*
    public function processConviction($data)
    {
        $data = array_merge($data, $data['defendant-details'], $data['offence']);
        unset(
            $data['defendant-details'],
            $data['cancel-conviction'],
            $data['offence'],
            $data['save'],
            $data['cancel'],
            $data['conviction'],
            $data['conviction-operator']
        );
        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit') {
            unset($data['vosaCase']);
            $result = $this->processEdit($data, 'Conviction');
        } else {
            $result = $this->processAdd($data, 'Conviction');
        }

        return $this->redirect()->toRoute(
            'case_convictions',
            array(
                'case' => $routeParams['case'], 'licence' => $routeParams['licence']
            )
        );
    }
 */
}
