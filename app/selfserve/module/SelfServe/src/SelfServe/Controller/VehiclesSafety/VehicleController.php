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
        
        $view = $this->getView();

        // render the view
        $view->setVariables(['form' => $form, 'goodsOrPsv' => $goodsOrPsv]);
        $view->setTemplate('self-serve/vehicle-safety/add-vehicle');
        return $view;

    }
    
    public function getView()
    {
        return new ViewModel();
    }
    
    /**
     * Generates the edit form 
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        $applicationId = $this->params()->fromRoute('applicationId');
        $vehicleId = $this->params()->fromRoute('vehicleId');

        $licence = $this->_getLicenceEntity();
        echo 'edit vehicle';
        exit;

    }
    
    /**
     * Process adding of goods vehicle form
     * 
     * @param array $valid_data
     * @param \Zend\Form\Form $form
     * @param array $params
     * @return \Zend\Form
     */
    public function processAddGoodsVehicle($valid_data, \Zend\Form\Form $form, $params)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();
        $save_result = $this->persistVehicle($valid_data);
        
        if ($save_result)
        {
            // check for submit buttons and redirect accordingly
            $posted_data = $this->getRequest()->getPost()->toArray();  

            if (array_key_exists('submit_add_another', $posted_data))
            {
                $this->redirect()->toRoute('selfserve/vehicle-action/vehicle-add', 
                                    array('action' => 'add', 'applicationId' => $applicationId));            
            }
            else 
            {
                $this->redirect()->toRoute('selfserve/vehicles-safety', array('applicationId' => $applicationId));        
            }

        }
        
        return $form;
    }
  
    /**
     * Method to persist the vehicle and licence vehicle entity data
     * 
     * @param array $valid_data
     * @throws \RuntimeException
     */
    private function persistVehicle($valid_data)
    {
        try
        {
            $licence = $this->_getLicenceEntity();
            
            $vehicle_data = array(
                    'version' => 1,
                    'vrm' => $valid_data['vrm'],
                    'platedWeight' => (int) $valid_data['plated_weight'],
                    'bodyType' => $valid_data['body_type'],
                    'isTipper' => 0,
                    'isRefrigerated' => 0,
                    'isArticulated' => 0,
                    'certificateNumber' => '',
                    'viAction' => ''
            );
            // store the vehicle
            $vehicle = $result = $this->makeRestCall('Vehicle', 'POST', $vehicle_data);

            // store the licence_vehicle
            $licence_vehicle_data = array(
                'licence' => $licence['id'],
                'dateApplicationReceived' => date('Y-m-d H:i:s'),
                'vehicle' => $vehicle['id'],
                'version' => 1
            );

            $licence_vehicle_result = $lv_result = $this->makeRestCall('LicenceVehicle', 'POST', $licence_vehicle_data);

            
        } catch (Exception $ex) {
            return false;
        }
        
        return true;
        
    }
    
    /**
     * End of the journey redirect to vehicle finance landing page
     */
    public function completeAction()
    {

    }
   
}
