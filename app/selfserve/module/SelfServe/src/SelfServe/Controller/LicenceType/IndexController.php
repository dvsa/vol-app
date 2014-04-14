<?php

/**
 * licence type Controller
 *
 *
 * @package		selfserve
 * @subpackage          operating-centre
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\LicenceType;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormJourneyActionController{
    
    /**
     * Construct the LicenceType Controller class
     * Sets the current section only.
     */
    public function __construct()
    {
        $this->setCurrentSection('licence-type');
    }
    
    /**
     * Generates the next step form depending on which step the user is on.
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function generateStepFormAction() {
        $licenceId = $this->params()->fromRoute('licenceId');
        $step = $this->params()->fromRoute('step');

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
        }
        
        // render the view
        $view = new ViewModel(['licenceTypeForm' => $form]);
        $view->setTemplate('self-serve/licence/index');
        return $view;
    }
    
		
    /**
     * Method to process the operator location. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOperatorLocation($valid_data, $form, $params)
    {
        $operatorLocation = $valid_data['operator_location']['operator_location'];
        $data = array(
        	'id' => $params['licenceId'],
            'niFlag' => $operatorLocation == 'ni' ? 1 : 0,
            'version' => $valid_data['version'],
        );
        
        //if location is Norther Ireland, the operator type is always goods
        if ($operatorLocation == 'ni'){
            $data['goodsOrPsv'] = 'goods';
        }
        
        $this->processEdit($data, 'Licence');
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type', 
                                    array('licenceId' => $params['licenceId'], 'step' => $next_step));
        
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getOperatorLocationFormData()
    {   
    	$entity = $this->_getLicenceEntity();
    	if (is_null($entity['niFlag']))
    	    return array('version' => $entity['version']);
    	
        return array(
            'version' => $entity['version'],
            'operator_location' => array(
    	        'operator_location' => $entity['niFlag'] ? 'ni' : 'uk', 
            ),
    	);
    }
    
    
    
    /**
     * Method to process the operator type. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOperatorType($valid_data, $form, $params)
    {
    	$licenceId = $params['licenceId'];
        $data = array(
        	'goodsOrPsv' => $valid_data['operator-type']['operator-type'],
        	'id' => $licenceId,
            'version' => $valid_data['version'],
        );
        $this->processEdit($data, 'Licence');
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type',    
                                array('licenceId' => $licenceId, 
                                      'step' => $next_step));
    }
    
    
    /**
     * Returns persisted data (if exists) to popuplate form
     * 
     * @return array
     */
    public function getOperatorTypeFormData()
    {
    	$entity = $this->_getLicenceEntity();
    	
        return array(
            'version' => $entity['version'],
            'operator-type' => array(
    	        'operator-type' => $entity['goodsOrPsv'], 
            ),
    	);
    }
    
    
    /**
     * Method to process the licence type. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceType($valid_data, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
        	'licenceType' => $valid_data['licence-type']['licence_type'],
        	'id' => $licenceId,
            'version' => $valid_data['version'],
        );
        $this->processEdit($data, 'Licence');
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type-complete', 
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLicenceTypeFormData()
    {
    	$entity = $this->_getLicenceEntity();
    	
    	return array(
    	    'version' => $entity['version'],
    	    'licence-type' => array(
    		    'licence_type' => $entity['licenceType'],
    	    ),
    	);
    }
    
    /**
     * Method to process the licence type for PSV type operators 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceTypePsv($valid_data, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
        	'licenceType' => $valid_data['licence-type-psv']['licence-type-psv'],
        	'id' => $licenceId,
            'version' => $valid_data['version'],
        );
        $this->processEdit($data, 'Licence');
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type-complete', 
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
 
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLicenceTypePsvFormData()
    {
        $entity = $this->_getLicenceEntity();
    	
    	return array(
    	    'version' => $entity['version'],
    	    'licence-type-psv' => array(
    		    'licence-type-psv' => $entity['licenceType'],
    	    ),
    	);
    }
    
    /**
     * Method to process the licence type for NI.
     * Should insist that goods_or_psv = goods? 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceTypeNi($valid_data, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
        	'licenceType' => $valid_data['licence-type-ni']['licence_type'],
        	'id' => $licenceId,
            'version' => $valid_data['version'],
        );
        $this->processEdit($data, 'Licence');

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type-complete',  
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
 
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLicenceTypeNiFormData()
    {
        $entity = $this->_getLicenceEntity();
        
    	return array(
    	    'version' => $entity['version'],
    	    'licence-type-ni' => array(
    		    'licence_type' => $entity['licenceType'],
    	    ),
    	);
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
    
    /**
     * Get licence entity based on route id value
     *
     * @return array|object
     */
    protected function _getLicenceEntity()
    {
        $licenceId = (int) $this->params()->fromRoute('licenceId');
        return $this->makeRestCall('Licence', 'GET', array('id' => $licenceId));
    }
   
}
