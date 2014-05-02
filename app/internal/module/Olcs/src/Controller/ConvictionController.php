<?php

/**
 * Conviction controller
 *
 * Manages convictions
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Conviction controller
 *
 * Manages convictions
 */
class ConvictionController extends FormActionController
{
    public function dealtAction()
    {
        $params = $this->getParams(['id', 'case', 'licence']);

        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return $this->notFoundAction();
        }

        $case = $this->makeRestCall('Conviction', 'GET', array('id' => $params['id']));

        $data = array_intersect_key($case, array_flip(['id', 'version']));
        $data['dealtWith'] = 'Y';

        $this->processEdit($data, 'Conviction');

        return $this->redirect()->toRoute(
            'case_convictions',
            [
                'case' => $params['case'],
                'licence' => $params['licence']
            ]
        );
    }

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

        //two unsets here keeps line length under 120
        //keeps phpunit happy as it isn't detecting the code has
        //been run when the parameters on on more than one line!
        unset(
            $data['defendant-details'], $data['cancel-conviction'], $data['offence'], $data['save']
        );

        unset(
            $data['cancel'], $data['conviction'], $data['conviction-operator']
        );

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit' || strtolower($routeParams['action']) == 'dealt') {
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
