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

class IndexController extends FormJourneyActionController
{
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
       
        $step = $this->params()->fromRoute('step');

        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // check for submit buttons
        $submit_posted = $this->determineSubmitButtonPressed($this->getRequest());

        // Do the post if required
        switch($submit_posted)
        {
            case 'lookup_company':
                $form->setValidationGroup(['registered_company' => ['company_number']]);
                $form = $this->formPost($form, 'processLookupCompany');
                break;
            case 'add_trading_name':
                $form->setValidationGroup(['registered_company' => ['trading_names']]);
                $form = $this->formPost($form, 'processAddTradingName');
                break;
            default:
                // do nothing since we already have the form?
                //$form->setValidationGroup(InputFilterInterface::VALIDATE_ALL);
                $form = $this->formPost($form, 'processForm');
                break;
        }
        
        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
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

    public function companyLookupAction()
    {
        
    }
    
    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to 
     * indexAction.
     */
    protected function processLookupCompany()
    {
        echo 'FORM VALID looking up company';
        // 
        exit;
    }
    
    /**
     * Method called once a valid company look up form has been submitted.
     * 
     */
    protected function processAddTradingName()
    {
        echo 'FORM VALID adding trading name';

        exit;    
        
    }
    
    protected function processAll()
    {
        // Main processing form
        
        // persist data if possible
        
        $this->redirect()->toRoute('selfserve/finance', ['step' => 'index']);
    }
}
