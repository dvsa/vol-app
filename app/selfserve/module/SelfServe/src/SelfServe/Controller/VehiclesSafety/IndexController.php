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
        
        // process any submit button pressed
        if ($this->getRequest()->isPost())
        {
            $action = $this->getRequest()->getPost('action');
            $vehicleId = $this->getRequest()->getPost('id');
            $applicationId = $this->params()->fromRoute('applicationId');

            $this->redirect()->toRoute('selfserve/vehicle-action/vehicle-'.strtolower($action), 
                    array(  'action' => $action, 
                            'vehicleId' => $vehicleId, 
                            'applicationId' => $applicationId
                         )
            );
        }
        
        $licence = $this->_getLicenceEntity();
        $vehicleTable = $this->generateVehicleTable($licence);
        
        // render the view
        $view = new ViewModel(['vehicleTable' => $vehicleTable]);
        $view->setTemplate('self-serve/vehicle-safety/index');
        return $view;
    }
    
    private function generateVehicleTable($licence)
    {
        $results = $this->makeRestCall('LicenceVehicle', 'GET', array('licence' => $licence['id']));

        $settings = array(
            'sort' => 'field',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url') // The helper needs a URL object to build the URL for sorting, pagination, limit etc
        );
  
        $table = $this->getServiceLocator()->get('Table')->buildTable('vehicle', $results, $settings);
        return $table;
    }
    
    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {

    }
   
}
