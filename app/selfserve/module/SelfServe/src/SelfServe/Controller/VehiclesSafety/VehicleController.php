<?php

/**
 * Vehicles Controller - responsible for CRUD vehicles
 *
 * @package		selfserve
 * @subpackage          vehicle-safety
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\VehiclesSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

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
     * Method to add vehicle
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction() 
    {
        $licence = $this->_getLicenceEntity();

        $form = $this->generateForm(
                'update-vehicle', 'processAddGoodsVehicleForm'
        );
         
        $goodsOrPsv = $licence['goodsOrPsv'] == 'PSV' ? 'psv' : 'goods';
        
        $view = $this->getViewModel(['form' => $form, 'goodsOrPsv' => $goodsOrPsv]);
        $view->setTemplate('self-serve/vehicle-safety/add-vehicle');
        
        return $view;
    }
    
    /**
     * Method to edit a vehicle.
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $licence = $this->_getLicenceEntity();

        $goodsOrPsv = $licence['goodsOrPsv'] == 'PSV' ? 'psv' : 'goods';

        $vehicleId  = $this->params()->fromRoute('vehicleId');
        
        $data = array(
        	'id' => $vehicleId,
        );
        
        //get operating centre enetity based on applicationId and operatingCentreId
        $result = $this->makeRestCall('Vehicle', 'GET', $data);

        if (empty($result)){
            return $this->notFoundAction();
        }

        //hydrate data
        $data = array(
            'id' => $result['id'],
            'version' => $result['version'],
            'vrm' => $result['vrm'],
            'plated_weight' => $result['platedWeight'],
            'body_type' => $result['bodyType']
        );

        
        // generate form with data
        $form = $this->generateFormWithData(
                'update-vehicle', 'processEditGoodsVehicleForm', $data
        );

        $view = $this->getViewModel(['form' => $form, 'goodsOrPsv' => $goodsOrPsv]);
        $view->setTemplate('self-serve/vehicle-safety/add-vehicle');
        
        return $view;
    }
    
    /**
     * Performs delete action
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function deleteAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $vehicleId = $this->params()->fromRoute('vehicleId');
        $licence = $this->_getLicenceEntity();
        
        //delete conditions
        $cond = array(
            'vehicle' => $vehicleId, 
            'licence' => $licence['id'],
        );
        $licenceVehicle = $this->makeRestCall('LicenceVehicle', 'GET', $cond);
        
        if ($licenceVehicle['Count'] == 0){
            return $this->notFoundAction();
        }
        $licenceVehicle = $licenceVehicle['Results'][0];
        $result = $this->makeRestCall('LicenceVehicle', 'DELETE', ['id' => $licenceVehicle['id']]);

        $this->makeRestCall('Vehicle', 'DELETE', ['id' => $vehicleId]);

        return $this->redirect()->toRoute('selfserve/vehicle-safety', array('applicationId' => $applicationId));
    }
    
    /**
     * Process adding of goods vehicle form
     * 
     * @param array $valid_data
     * @param \Zend\Form\Form $form
     * @param array $params
     * @return \Zend\Form
     */
    public function processAddGoodsVehicleForm($validData, \Zend\Form\Form $form, $params)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();
        $saveResult = $this->persistVehicle($validData);
        
        if ($saveResult)
        {
            $postedData = $this->getRequest()->getPost()->toArray();  
            $this->determineRedirect($postedData);
        }
        
        return $form;
    }
  
    /**
     * Persist data to database. After that, redirect to landing page
     *
     * @param array $validData
     * @return void
     */
    public function processEditGoodsVehicleForm($validData)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();
        $saveResult = $this->persistVehicle($validData);
        
        if ($saveResult)
        {
            $postedData = $this->getRequest()->getPost()->toArray();  
            $this->determineRedirect($postedData);

        }
        
        return $saveResult;
        
    }
    
    /**
     * Method to examine the submit_ button that has been pressed and redirect
     * to the correct route.
     * 
     * @param array $postedData
     */
    private function determineRedirect($postedData)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        if (array_key_exists('submit_add_another', $postedData))
        {
            $this->redirect()->toRoute('selfserve/vehicle-safety/vehicle-action/vehicle-add', 
                                array('action' => 'add', 'applicationId' => $applicationId));            
        }
        else 
        {
            $this->redirect()->toRoute('selfserve/vehicle-safety', array('applicationId' => $applicationId));        
        }
    }
    
    /**
     * Method to persist the vehicle and licence vehicle entity data
     * 
     * @param array $valid_data
     * @throws \RuntimeException
     */
    private function persistVehicle($validData)
    {

        try
        {
            if (isset($validData['id']) && is_numeric($validData['id']))
            {
                $this->updateVehicle($validData);
            }
            else 
            {
                $this->createVehicle($validData);
            }
        } 
        catch (Exception $ex)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Method to create the vehicle Entity onlt
     * 
     * @param array $validData ['id' => ?]
     * @return array containing id
     */
    private function createVehicle($validData)
    {
        $vehicleData = $this->mapVehicleData($validData);
        $vehicle = $result = $this->makeRestCall('Vehicle', 'POST', $vehicleData);

        $licence = $this->_getLicenceEntity();

        $this->createLicenceVehicle($licence, $vehicle);
        
        return $vehicle;
    }
    
    /**
     * Method to update a vehicle Entity
     * 
     * @param array $validData ['id' => ?]
     * @return array containing id
     */
    private function updateVehicle($validData)
    {
        $vehicleData = $this->mapVehicleData($validData);
        $vehicle = $result = $this->makeRestCall('Vehicle', 'PUT', $vehicleData);
        
        return $vehicle;
    }
    
    /** 
     * Method to map form data to vehicle data
     * 
     * @param array $validData form data
     * @return array entity data
     */
    private function mapVehicleData($validData)
    {
        $vehicleData = array(
            'id' => $validData['id'],
            'version' => $validData['version'],
            'vrm' => $validData['vrm'],
            'platedWeight' => (int) $validData['plated_weight'],
            'bodyType' => 'vhl_body_type.flat', //$validData['body_type'], //NOT PART OF THE STORY (2057)
            'isTipper' => 0,
            'isRefrigerated' => 0,
            'isArticulated' => 0,
            'certificateNumber' => '',
            'viAction' => ''
        );
        return $vehicleData;
    }
    
    /** 
     * Method to map form data to licencevehicle data
     * 
     * @param array $validData form data
     * @return array entity data
     */
    private function mapLicenceVehicleData($licence, $vehicle)
    {
        $licenceVehicleData = array(
            'licence' => $licence['id'],
            'dateApplicationReceived' => date('Y-m-d H:i:s'),
            'vehicle' => $vehicle['id'],
            'version' => 1
        );
        return $licenceVehicleData;
    }
    
    /**
     * Method to create the licence vehicle in the database
     * 
     * @param array $licence licence data
     * @param array $vehicle vehicle data
     */
    private function createLicenceVehicle($licence, $vehicle)
    {
        $licenceVehicleData = $this->mapLicenceVehicleData($licence, $vehicle);
        $licenceVehicleResult = $lv_result = $this->makeRestCall('LicenceVehicle', 'POST', $licenceVehicleData);
    }
    
    /**
     * End of the journey redirect to vehicle finance landing page
     */
    public function completeAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        // persist data if possible
        $this->redirect()->toRoute('selfserve/vehicle-safety', 
                                array('applicationId' => $applicationId, 'step' => 
                                 'index'));           
    }
    

    /**
     * Method to return a new view model - to make testing easier?
     * 
     * @return \Zend\View\Model\ViewModel
     */
    private function getViewModel($params)
    {
        return new ViewModel($params);
    }
}
