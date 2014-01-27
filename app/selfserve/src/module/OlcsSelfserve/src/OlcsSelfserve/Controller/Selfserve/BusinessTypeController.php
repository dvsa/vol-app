<?php
/**
 * @file BusinessTypeController.php
 * 
 * Business type Controller.
 *
 * OLCS-865
 *
 * @package         olcs-selfserve
 * @subpackage      application
 * @author          S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsSelfserve\Controller\Selfserve;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OlcsSelfserve\Form;
use Zend\Session\Container;
use Zend\Log\Logger;
use DateTime;

class BusinessTypeController extends AbstractActionController
{

    public $messages = null;

    protected $applicationId;
    protected $application;
    
    /**
     * Method to initialise the controller by looking for passed params and 
     * preloading data required, if these params are set.
     * Preloads application/licence and organisation 
     * 
     * @return null
     *
    public function init()
    {
        $this->logger = $this->getServiceLocator()->get('Zend\Log');
        
        // get application
        $this->applicationId = $this->getEvent()->getRouteMatch()->getParam('appId');
     
        if (isset($this->applicationId) && is_numeric($this->applicationId))
        {
            
            $appService = $this->service('Olcs\Application');
            $this->application = $appService->get($this->applicationId);
            $this->logger->err('Application '.$this->applicationId.' could not be retrived');
            
            // if application has a licence, get the licence
            if (isset($this->application['applicationLicence']['licenceId']))
            {
                $this->licenceId = $this->application['applicationLicence']['licenceId'];
                $this->licence = $this->service('Olcs\Licence')->get($this->licenceId);
                
                $this->logger->err('Licence '.$this->licenceId.' could not be retrived');
                
                // if licence has an operator get the organisation
                if (isset($this->licence['operator']['operatorId']))
                {
                    $this->organisation = $this->service('Olcs\Organisation')->get($this->licence['operator']['operatorId']);            
                }
            }
        }

    }*/
    
   /**
    * Method to dispatch the business type form based on the type of business
    * 
    * @return type
    */
   public function detailsAction() 
    {

        $businessDetailsForm = new Form\Application\BusinessDetailsForm();

        $view = new ViewModel(array('businessDetailsForm' => $businessDetailsForm,
                                    'messages' => $this->messages
                                ));

        $view->setTemplate('application/business-details');
        return $view;        
        
    }
    
   
}
