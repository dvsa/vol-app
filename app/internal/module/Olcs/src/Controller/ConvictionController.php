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
    public function addEditAction()
    {
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(array('operators/operators-params' => array('operatorName' => 'a')));
        
        $routeParams = $this->getParams(array('case', 'licence', 'formAction', 'id'));
        
        $data = array('case' => $routeParams['case']);
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        
        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
             return $this->getResponse()->setStatusCode(404);
        }
        
        if (strtolower($routeParams['formAction']) == 'edit' && isset($routeParams['id'])) {
            $data = $this->makeRestCall('Conviction', 'GET', array('id' => $routeParams['id']));
            $data['id'] = $routeParams['id'];
            $data['defendant-details'] = $data;
            $data['offence'] = $data;
        }
        $form = $this->generateFormWithData(
            'conviction', 'addConviction', $data
        );

        $view = new ViewModel([
            'form' => $form,
            'params' => [
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'add-conviction-text'
                ]
            ]
        );
        $view->setTemplate('form');
        return $view;
    }
    
    public function editAction() {
        
    }
    
    protected function addConviction($data) 
    {
        $data = array_merge($data, $data['defendant-details'], $data['offence']);
        unset($data['defendant-details'], $data['offence'], $data['case'], $data['save-add'], $data['save'], $data['cancel']);
        $routeParams = $this->getParams(array('case', 'licence', 'formAction', 'id'));
        if (strtolower($routeParams['formAction']) == 'edit') {
            $result = $this->processEdit($data, 'Conviction');
        } else {
            $result = $this->processAdd($data, 'Conviction');
        }
        $this->redirect()->toRoute('search', array());
    }

}
