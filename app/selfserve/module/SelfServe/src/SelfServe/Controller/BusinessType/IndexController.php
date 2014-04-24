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
       
        $applicationId = $this->params()->fromRoute('applicationId');
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
                $form = $this->formPost($form, 'processLookupCompany',['applicationId' => $applicationId]);
                break;
            case 'add_trading_name':
                $form->setValidationGroup([$step => ['trading_names']]);
                $form = $this->formPost($form, 'processAddTradingName',['applicationId' => $applicationId]);
                break;
            default:
                $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['applicationId' => $applicationId]);
                break;
        }
        

        // render the view
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/business/index');
        return $view;
    }

    /**
     * End of the journey redirect to finance type
     * 
     * @todo THIS IS WRONG! On all journey we should relay on application entity 
     * not on a licence. So this is a temporary hack, that creates application entity 
     */
    public function completeAction()
    {
        $licenceId = $this->params('licenceId');
        
        //create application entity (hack)
        $data = array(
        	'version'   => 1,
            'licence'   => $licenceId,
            'status'    => 'app_status.new', 
        );
        $result = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $result['id'];
        
        $this->redirect()->toRoute('selfserve/finance', ['step' => 'index', 'applicationId' => $applicationId]);
    }
    
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getBusinessTypeFormData()
    {
        $organisation = $this->_getOrganisationEntity();
        
        return array(
                'version' => $organisation['version'],
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
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'organisationType' => $valid_data['business-type']['business-type'],
            'version' => $valid_data['version'],
        );
       
        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
        
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getRegisteredCompanyFormData()
    {
        $organisation = $this->_getOrganisationEntity();
        
        return array(
                'version' => $organisation['version'],
                'registered-company' => array(
                        'company_number' => $organisation['registeredCompanyNumber'],
                        'company_name' => $organisation['name'],
                        'type_of_business' => $organisation['sicCode'],
                ),
        );
    }
    
    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processRegisteredCompany($valid_data, $form, $params)
    {
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
                'id' => $licence['id'],
                'name' => $valid_data['registered-company']['company_name'],
                'registeredCompanyNumber' => $valid_data['registered-company']['company_number'],
                'sicCode' => $valid_data['registered-company']['type_of_business'],
                'version' => $valid_data['version'],
        );
        
        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
    
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
    
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getSoleTraderFormData()
    {
        $organisation = $this->_getOrganisationEntity();
        return array(
                'version' => $organisation['version'],
                'sole-trader' => array(
                        'type_of_business' => $organisation['sicCode'],
                        //'trading_names' => $organisation['name'],
                ),
        );
    }
    
    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processSoleTrader($valid_data, $form, $params)
    {
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
                'id' => $licence['id'],
                'sicCode' => $valid_data['sole-trader']['type_of_business'],
                'version' => $valid_data['version'],
        );

        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
    
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
    
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getPartnershipFormData()
    {
        $organisation = $this->_getOrganisationEntity();
    
        return array(
                'version' => $organisation['version'],
                'partnership' => array(
                        'company_name' => $organisation['name'],
                        'type_of_business' => $organisation['sicCode'],
                        //'trading_names' => $organisation['name'],
                ),
        );
    }
    
    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processPartnership($valid_data, $form, $params)
    {
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
                'id' => $licence['id'],
                'name' => $valid_data['partnership']['company_name'],
                'sicCode' => $valid_data['partnership']['type_of_business'],
                'version' => $valid_data['version'],
        );
         
        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
    
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
    
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLlpFormData()
    {
        $organisation = $this->_getOrganisationEntity();
    
        return array(
                'version' => $organisation['version'],
                'llp' => array(
                        'company_number' => $organisation['registeredCompanyNumber'],
                        //'trading_names' => $organisation['name'],
                ),
        );
    }
    
    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLlp($valid_data, $form, $params)
    {
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
                'id' => $licence['id'],
                'registeredCompanyNumber' => $valid_data['llp']['company_number'],
                'version' => $valid_data['version'],
        );
         
        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
    
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
    
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getPublicAuthorityFormData()
    {
        $organisation = $this->_getOrganisationEntity();
    
        return array(
                'version' => $organisation['version'],
                'public-authority' => array(
                        'company_name' => $organisation['name'],
                        'type_of_business' => $organisation['sicCode'],
                        //'trading_names' => $organisation['name'],
                ),
        );
    }
    
    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processPublicAuthority($valid_data, $form, $params)
    {
        $licence = $this->_getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
                'id' => $licence['id'],
                'name' => $valid_data['public-authority']['company_name'],
                'sicCode' => $valid_data['public-authority']['type_of_business'],
                'version' => $valid_data['version'],
        );
         
        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);
    
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('applicationId' => $applicationId, 'step' => $next_step));
    
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getOtherFormData()
    {
        $organisation = $this->_getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'other' => array(
                'company_name' => $organisation['name'],
                'type_of_business' => $organisation['sicCode'],
                //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOther($valid_data, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
            'id' => $licenceId,
            'name' => $valid_data['other']['company_name'],
            'sicCode' => $valid_data['other']['type_of_business'],
            'version' => $valid_data['version'],
        );

        $result = $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/business-type', array('licenceId' => $licenceId, 'step' => $next_step));

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
     * Get organisation entity based on licenceId 
     * 
     * @return array
     */
    private function _getOrganisationEntity()
    {
        $licence = $this->_getLicenceEntity();

        $result = $this->makeRestCall('LicenceOrganisation', 'GET', array('id' => $licence['id']));
        return $result;
    }

}
