<?php

/**
 * Conviction controller
 *
 * Add and edit convictions
 *
 * @package    olcs
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Search controller
 *
 * Search for operators and licences
 *
 * @package    olcs
 * @author     Mike Cooper
 * @author     Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionController extends FormActionController
{

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(array('operators/operators-params' => array('operatorName' => 'a')));
        
        $routeParams = $this->getParams(array(
            'case',
            'licence',
            'id'
            ));
        
        $data = array('case' => $routeParams['case']);
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        
        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
             return $this->getResponse()->setStatusCode(404);
        }
        
        $form = $this->generateForm(
                    'conviction', 
                    'processConviction'
        );
        $form->setData($data);
        //$form->setMessages(array('blah' => 'This is a test message'));
        $view = new ViewModel([
            'form' => $form,
            'headScript' => ['/static/js/conviction.js'],
            'params' => [
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'add-conviction-text'
                ]
            ]
        );
        $view->setTemplate('conviction/form');
        return $view;
    }
    
    public function editAction() 
    {
        $routeParams = $this->getParams(array(
            'case', 
            'licence',  
            'id',
            'change'
        ));
        
        $data = $this->makeRestCall('Conviction', 'GET', array('id' => $routeParams['id']));
        $data['id'] = $routeParams['id'];
        $data['defendant-details'] = $data;
        $data['offence'] = $data;
        
        $form = $this->generateFormWithData(
                    'conviction', 
                    'processConviction',
                    $data
        );
        
        $view = new ViewModel([
            'form' => $form,
            'headScript' => ['/static/js/conviction.js'],
            'params' => [
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'add-conviction-text'
                ]
            ]
        );
        $view->setTemplate('conviction/form');
        return $view;
    }
    
    protected function processConviction($data, $action) 
    {
        $data = array_merge($data, $data['defendant-details'], $data['offence']);
        //$data['cases'] = array($data['case']);
        unset($data['defendant-details'], $data['offence'], $data['case'], $data['save'], $data['cancel'], $data['conviction'], $data['conviction-operator']);
        $routeParams = $this->getParams(array('action', 'licence', 'case'));
//print_r($data);
//exit;
        if (strtolower($routeParams['action']) == 'edit') {
            $result = $this->processEdit($data, 'Conviction');
        } else {
            $result = $this->processAdd($data, 'Conviction');
        }
        
        return $this->redirect()->toRoute('conviction', array(
            'licence' => $routeParams['licence'],
            'case' =>  $routeParams['case'],
            'action' => 'add'
        ));
    }

}
