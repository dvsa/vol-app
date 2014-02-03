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
     */
    public function init()
    {
        //$this->logger = $this->getServiceLocator()->get('Zend\Log');
        
        // get application
        $this->applicationId = $this->getEvent()->getRouteMatch()->getParam('applicationId');

        if (isset($this->applicationId) && is_numeric($this->applicationId))
        {
            try 
            {
                $appService = $this->service('Olcs\Application');
                $this->application = $appService->get($this->applicationId);
                //$this->logger->err('Application '.$this->applicationId.' could not be retrived');
            }
            catch (Exception $e)
            {
                return $e;
            }

            
            // if application has a licence, get the licence
            if (isset($this->application['applicationLicence']['licenceId']))
            {
                try 
                {
                    $this->licenceId = $this->application['applicationLicence']['licenceId'];
                    $this->licence = $this->service('Olcs\Licence')->get($this->licenceId);
                }
                catch (Exception $e)
                {
                    //$this->logger->err('Licence '.$this->licenceId.' could not be retrived');
                    return $e;
                }
                
                // if licence has an operator get the organisation
                if (isset($this->licence['operator']['operatorId']))
                {
                    try 
                    {
                        $this->organisation = $this->service('Olcs\Organisation')->get($this->licence['operator']['operatorId']);            
                    }
                    catch (Exception $e)
                    {
                        //$this->logger->err('Organisation '.$this->licence['operator']['operatorId'].' could not be retrived');
                        return $e;
                    }
                }
            }
        }
  
    }
    
   /**
    * Method to dispatch the business type form based on the type of business
    * 
    * @return type
    */
   public function detailsAction() 
    {
        $this->init();
        $businessDetailsForm = new Form\Application\BusinessDetailsForm();

        if ($this->getRequest()->isPost())
        {
            // FORM POSTED
            if ($businessDetailsForm->isValid())
            {
                $this->process();
            } 
            // invalid form, todo error messages set in isValid
        }
        else 
        {           
            $formData = $this->mapFormData();

            // prefill form
            $businessDetailsForm->setData($formData);
        }
        
        $view = new ViewModel(array('businessDetailsForm' => $businessDetailsForm,
                                    'messages' => $this->messages
                                ));

        $view->setTemplate('selfserve/application/businessDetails');
        return $view;        
        
    }
    
    /**
     * Method to map form data fields from any data retrieved from the database.
     * Enabling an easy $form->setData($formData) to be used.
     * 
     * @return array
     */
    private function mapFormData() {
        $formData = array();
        
        if (isset($this->licence))
        {
            $formData['entityType'] = $this->licence['operator']['entityType'];
            $formData['companyNumId'] = $this->licence['operator']['registeredCompanyNumber'];
            $formData['operatorId'] = $this->licence['operator']['operatorId'];
            $formData['operatorNameTextHidden'] = $this->licence['operator']['operatorName'];
            $formData['operatorName'] = $this->licence['operator']['operatorName'];
            $formData['tradingDropdown'] = $this->licence['tradeType'];
            $formData['tradingNames'] = $this->licence['tradingNames'];
        }
        
        return $formData;
    }
    
    /**
     * Update the database and redirect
     * @todo
     */
    public function process()
    {
        
        return true;
        
    }
    
}
