<?php

/**
 * Vehicles Controller - responsible for CRUD vehicles
 *
 * @package		selfserve
 * @subpackage          vehicles-safety
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\VehiclesSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;
use SelfServe\SelfServeTrait;

class VehicleController extends FormJourneyActionController{
    
    /**
     * Construct the Vehicles Safety Controller class
     * Sets the current section only.
     */
    public function __construct()
    {
        $this->setCurrentSection('update-vehicle');
    }
    
    /**
     * Generates the next step form depending on which step the user is on.
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction() {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();
        
        if ($licence['goodsOrPsv'] == 'PSV')
        {
            $step = 'add-psv-vehicle';
            $goodsOrPsv = 'psv';
        }
        else 
        {
            $step = 'add-goods-vehicle';
            $goodsOrPsv = 'goods';
        }
        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['applicationId' => $applicationId]);

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }
        
        // render the view
        $view = new ViewModel(['form' => $form, 'goodsOrPsv' => $goodsOrPsv]);
        $view->setTemplate('self-serve/vehicle-safety/add-vehicle');
        return $view;

    }
    
    public function processAddGoodsVehicle($valid_data, $form, $params)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();
        $vehicle_data = array(
                'version' => 1,
                'vrm' => $valid_data['vrm'],
                'plated_weight' => $valid_data['plated_weight'],
                'body_type' => $valid_data['body_type']
        );

        $vehicle = $result = $this->makeRestCall('Vehicle', 'POST', $vehicle_data);
 
        // check for submit buttons
        $submit_posted = $this->determineSubmitButtonPressed($this->getRequest());
var_dump($submit_posted);
        $this->redirect()->toRoute('selfserve/vehicles-safety', array('applicationId' => $applicationId));
    
    }
    
    /**
     * End of the journey redirect to vehicle finance landing page
     */
    public function completeAction()
    {

    }
   
}
