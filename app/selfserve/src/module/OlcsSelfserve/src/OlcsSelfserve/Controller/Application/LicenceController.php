<?php

/**
 * Create a new application - Licence details
 *
 * OLCS-666
 *
 * @package		olcs
 * @subpackage          application
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;
use Zend\Session\Container;

class LicenceController extends AbstractActionController
{

    public $messages = null;
        
    protected $licence;
    protected $applicationId;
    protected $application;

    /**
     * Licence details page of the create application process, this page requests the details
     * associated with the licence and returns forms to extract/display the correspondence details
     * Associtated with that licence.
     */
    public function detailsAction() {

        $request = $this->getRequest();
        $this->applicationId = $this->getEvent()->getRouteMatch()->getParam('appId');
        
        $appService = $this->service('Olcs\Application');
        $this->application = $appService->get($this->applicationId);

        if (empty($this->application)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }   

        $this->licenceId = $this->application['applicationLicence']['licenceId'];
        $this->licence = $this->service('Olcs\Licence')->get($this->licenceId);

        if ($this->getRequest()->isPost()) {
            $this->processForm();
        }
        
        // set navigation
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'licence details');

        // set up forms

        $correspondenceAddressForm = new Form\Application\CorrespondenceAddressForm();
        $correspondenceAddressForm->setAttribute('action', '/application/'.$this->applicationId.'/licence-details');

        if (isset($this->licence['contactDetails']['correspondence'])) $correspondenceAddressForm->setAttribute('class', 'hidden');
        
        $correspondenceFormView = new ViewModel(array('form' => $correspondenceAddressForm,
                                                      'messages' => $this->messages,
                                                      'licence' => $this->licence,
                                                     )
                                                );
        $correspondenceFormView->setTemplate('olcs/application/forms/correspondence-address-form');

        // declare parent view and add children
        $view = new ViewModel(array('applicationDetailsForm' => $correspondenceAddressForm,
                                    'application_header_details' => $this->getApplicationHeaderDetails($this->licence),
                                    'messages' => $this->messages,
                                    'active' => 'licenceDetailsActive',
                                    'licenceId' => $this->licenceId,
                                    'applicationId' => $this->applicationId
                                   )
                             );
        
        $view->setTemplate('olcs/application/application-details-wrapper');
        
        // add the child views
        $view->addChild($correspondenceFormView, 'forms', true);
        
        return $view;
        
    }

    /**
     * Method to process the form - adds the correspondence and establishement 
     * addresses if set. 
     */
    private function processForm() {
        $data = $this->params()->fromPost();

        if (isset($data['correspondence']) && is_array($data['correspondence'])) {
            $data['correspondence']['contactDetailsType'] = 'correspondence';
            $result = $correspondenceAddressId = $this->createAddress($data['correspondence']);
        }
        
        // only process if the establishmentAddressYN is set to no, implying 
        // the estblishment address is different from correspondence address
        if ($data['establishmentAddressYN'] == 'no' && (isset($data['establishment']) && is_array($data['establishment']))) {
            $data['establishment']['contactDetailsType'] = 'establishment';
            $correspondenceAddressId = $this->createAddress($data['establishment']);
        }
    }
    
    /**
     * Method takes data array and creates all required entities for an address
     * Includes the creation of a contact_details entity, address entity and 
     * contact licence entity
     * 
     * @param Array $data
     * @return type
     */
    private function createAddress(array $data) {
        
        // in progress
        $requestBody = array(
            'operator' => $this->licence['operator']['operatorId'],
            //'addressId' => $data['addressId'],
            'contactDetailsType' => $data['contact_type'],
            'address' => array(
                'line1' => $data['addressLine1'],
                'line2' => $data['addressLine2'],
                'line3' => $data['addressLine3'],
                'line4' => $data['addressLine4'],
                'town' => $data['townCity'],
                'country' => $data['country'],
                'postcode' => $data['postcode'],
                )         
            );
        if (isset($data['email'])) $requestBody['emailAddress'] = $data['email'];
        
        $result = $this->service('Olcs\Licence')->create('licence/createAddress', $requestBody);
        //echo '<p>calling service. Result-></p>';
        //var_dump($result);
        
        return $result['success'] == 1 ? $result : false;
      
    }
    
    /**
     * Method to extract the application details from the licence data
     * 
     * @return array
     */
    private function getApplicationHeaderDetails()
    {   
        //@todo revise this and check dates are correct
        return array('operatorTypes' => $this->licence['goodsOrPsv'],
                     'licenceTypes' => $this->licence['licenceType'],
                     'entityTypes' => $this->licence['operator']['entityType'],
                     'dateApplicationReceived' =>    date('d-m-Y', strtotime($this->application['applicationReceivedOn']['date'])),
                     'trafficAreaType' => $this->application['applicationTrafficArea']['areaname']
                    );
    }
    
}
