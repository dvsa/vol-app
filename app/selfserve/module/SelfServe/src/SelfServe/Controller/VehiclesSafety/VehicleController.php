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
        $step = 'add-vehicle';

        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['$applicationId' => $applicationId]);

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }
        
        // render the view
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/vehicle-safety/update-vehicle');
        return $view;

    }
    
    /**
     * End of the journey redirect to vehicle finance landing page
     */
    public function completeAction()
    {

    }
   
}
