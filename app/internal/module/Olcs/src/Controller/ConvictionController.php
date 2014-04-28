<?php

/**
 * Search controller
 *
 * Search for operators and licences
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class ConvictionController extends FormActionController
{

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id'));
        
        if (isset($_POST['cancel-conviction'])) {
            return $this->redirect()->toRoute('case_convictions', array('case' => $routeParams['case']));
        }
        
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_convictions' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );

        $data = array('vosaCase' => $routeParams['case']);
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateForm('conviction', 'processConviction');
        $form->setData($data);
        //$form->setMessages(array('blah' => 'This is a test message'));
        $view = new ViewModel(
            array(
            'form' => $form,
            'headScript' => array('/static/js/conviction.js'),
            'params' => array(
                'pageTitle' => 'add-conviction',
                'pageSubTitle' => 'add-conviction-text'
            )
            )
        );
        $view->setTemplate('conviction/form');
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
        if (isset($_POST['cancel-conviction'])) {
            return $this->redirect()->toRoute('case_convictions', array('case' => $routeParams['case']));
        }
        
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_convictions' => array('case' => $routeParams['case'])
            )
        );

        $bundle = array(
            'children' => array(
                'vosaCase' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $data = $this->makeRestCall('Conviction', 'GET', array('id' => $routeParams['id']), $bundle);
        if (isset($data['vosaCase'])) {
            $data['vosaCase'] = $data['vosaCase']['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $data['id'] = $routeParams['id'];
        $data['defendant-details'] = $data;
        $data['offence'] = $data;

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            $data,
            true
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'headScript' => array(
                    '/static/js/conviction.js'
                ),
                'params' => array(
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'add-conviction-text'
                )
            )
        );
        $view->setTemplate('conviction/form');
        return $view;
    }

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
                'case' => $routeParams['case'],
                'licence' => $routeParams['licence']
            )
        );
    }
}
