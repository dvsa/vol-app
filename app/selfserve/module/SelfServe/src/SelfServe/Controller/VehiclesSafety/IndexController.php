<?php

/**
 * Vehicles & Safety  Controller
 *
 *
 * @package		selfserve
 * @subpackage          vehicles-safety
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\VehiclesSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;
use SelfServe\SelfServeTrait;

class IndexController extends FormJourneyActionController{
    
    
    /**
     * Construct the Vehicles Safety Controller class
     * Sets the current section only.
     */
    public function __construct()
    {
        $this->setCurrentSection('vehicles-safety');
    }
    
    /**
     * Generates the next step form depending on which step the user is on.
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction() {
        
        $licence = $this->_getLicenceEntity();
        $vehicleTable = $this->generateVehicleTable($licence);
        
/*        $step = $this->params()->fromRoute('step');

        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['licenceId' => $licenceId]);

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }*/
        
        // render the view
        $view = new ViewModel(['vehicleTable' => $vehicleTable]);
        $view->setTemplate('self-serve/vehicle-safety/index');
        return $view;
    }
    
    private function generateVehicleTable($licence)
    {
        $results = $this->makeRestCall('LicenceVehicle', 'GET', array('licence_id' => $licence['id']));

        // These settings could come from the current route
        $settings = array(
            'sort' => 'field',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url') // The helper needs a URL object to build the URL for sorting, pagination, limit etc
        );
  
// There is a Table service, the build table takes in;
//  1. The name of the table config to use
//  2. The data which can be either
//         a simple array of results
//         or an array containing 'Count' and 'Results'
//  3. An array of settings
$table = $this->getServiceLocator()->get('Table')->buildTable('vehicle', $results, $settings);
    }
    
    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {
        $licenceId = $this->params()->fromRoute('licenceId');

        // persist data if possible
        $request  = $this->getRequest();
        $this->redirect()->toRoute('selfserve/business-type', 
                                array('licenceId' => $licenceId, 'step' => 
                                 'business-type'));
    }
   
}
