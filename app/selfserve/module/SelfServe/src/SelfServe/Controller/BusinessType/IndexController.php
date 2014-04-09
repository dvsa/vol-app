<?php

/**
 * Business Type Controller
 *
 *
 * @package		selfserve
 * @subpackage          business
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\BusinessType;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\FormInterface;
use \Zend\InputFilter\InputFilterInterface;
use SelfServe\SelfServeTrait;

class IndexController extends FormJourneyActionController
{
    
    use SelfServeTrait\FormJourneyTrait;
    
    protected $messages;
    
    public function __construct()
    {
        $this->setCurrentSection('business-type');
    }

    /**
     * Main method of processing the form. Generates a form and if a submit 
     * button is pressed, sets the validation group based on that button AND 
     * defines which callback should be used if the form is valid.
     *  
     * @return \Zend\View\Model\ViewModel
     */
    public function generateStepFormAction() {
       
        $licenceId = $this->params()->fromRoute('licenceId');
        $step = $this->params()->fromRoute('step');
        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }
        
        // check for submit buttons
        $submit_posted = $this->determineSubmitButtonPressed($this->getRequest());

        // Do the post if required
        switch($submit_posted)
        {
            case 'lookup_company':
                $form->setValidationGroup([$step => ['company_number']]);
                $form = $this->formPost($form, 'processLookupCompany',['licenceId' => $licenceId]);
                break;
            case 'add_trading_name':
                $form->setValidationGroup([$step => ['trading_names']]);
                $form = $this->formPost($form, 'processAddTradingName',['licenceId' => $licenceId]);
                break;
            default:
                // do nothing since we already have the form?
                //$form->setValidationGroup(InputFilterInterface::VALIDATE_ALL);

                if ($step == 'business-type')
                {
                    $form = $this->formPost($form, 'processBusinessType', ['licenceId' => $licenceId]);
                }
                else
                {
                    $form = $this->formPost($form, 'processAll', ['licenceId' => $licenceId]);    
                }
                    
                break;
        }
        

        // render the view
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/business/index');
        return $view;
    }

    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {
        // persist data if possible
        
        $this->redirect()->toRoute('selfserve/finance', ['step' => 'type']);
        
    }
    
    public function getBusinessTypeFormData()
    {
        return array();
        $organisation = $this->_getOrganisationEntity();
        if (empty($organisation))
            return array();
        
        //var_dump($organisation);exit;
         
        return array(
                'business-type' => array(
                        'business-type' => $organisation['organisationType'],
                ),
        );
    }
    
    /**
     * Method called as a callback once business type form has been validated.
     * Should redirect to the correct business type form page as the next step
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processBusinessType($valid_data, $form, $params)
    {
        // data persist goes here

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('licenceId' => $params['licenceId'], 'step' => $next_step));
        
    }
    
    public function getRegisteredCompanyFormData()
    {
        return array();
        $organisation = $this->_getOrganisationEntity();
        if (empty($organisation))
            return array();
    
        //\Zend\Debug\Debug::dump($organisation);exit;
         
        return array(
                'registered-company' => array(
                        'company_number' => $organisation['registeredCompanyNumber'],
                        'company_name' => $organisation['name'],
                        'trading_names' => $organisation['name'],
                ),
        );
    }
    
    
    
    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to 
     * indexAction.
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processLookupCompany($valid_data, $form, $params)
    {
        echo 'FORM VALID looking up company';
        // 
        exit;
    }
    
    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to 
     * indexAction.
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processAddTradingName($valid_data, $form, $params)
    {
        echo 'FORM VALID adding trading name';

        exit;    
        
    }
    
    /**
     * Method called for all business type forms submitted and found to be valid.
     * Uses PRG to redirect to the next step. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processAll($valid_data, $form, $params)
    {
        // Main processing form
        
        // persist data if possible
        
        $this->redirect()->toRoute('selfserve/finance', ['step' => 'index']);
    }
    
    private function _getOrganisationEntity()
    {
        $entity = $this->_getLicenceEntity();
        if (empty($entity))
            return array();
        
        if (is_null($entity['organisation'])){
           
            $data = array(
            	'name' => '',
            );
            
            /**
             * @todo update licence with organisationId
             */
            // create organisation
            $result = $this->makeRestCall('Organisation', 'POST', $data);
            $orgId = $result['id'];
            
            $result = $this->makeRestCall('Licence', 'PATCH', array(
                'id' => $entity['id'],
                'organisation' => $orgId, 
            ));
        }
        else{
            $orgId = $entity['organisation'];
        }
        
        $result = $this->makeRestCall('Organisation', 'GET', array('id' => $orgId));
        if (empty($result)) {
            //not found action?
            return false;
        }
        return $result;
    }

}
