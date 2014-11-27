<?php

/**
 * Transport Manager Details Detail Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Common\Service\Entity\ContactDetailsEntityService;

/**
 * Transport Manager Details Detail Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsDetailController extends AbstractTransportManagerDetailsController
{
    /**
     * @var string
     */
    protected $section = 'details-details';
    
    /**
     * Index action
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $tmId = $this->params()->fromRoute('transportManager');
        
        if ($this->isButtonPressed('cancel')) {
            $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
            return $this->redirectToRoute('transport-manager/details', ['transportManager' => $tmId]);
        }
        
        $form = $this->getForm('TransportManager');
        if (!$this->getRequest()->isPost()) {
            $tmData = $this->getServiceLocator()->get('Entity\TransportManager')->getTmDetails($tmId);
            $data = $this->formatDataForForm($tmData, $tmId);
            $form->setData($data);
        }

        $this->formPost($form, 'processSave');

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('transport-manager/details/tm-details');
        return $this->renderView($view);
    }
    
    /**
     * Format data for form
     *
     * @param array $data
     * @param int $tmId
     * @return array
     */
    protected function formatDataForForm($data, $tmId = '')
    {
        $tmDetails = [
            'id' => $tmId,
            'version' => isset($data['version']) ? $data['version'] : '',
            'type' => isset($data['tmType']['id']) ? $data['tmType']['id'] : ''
        ];
        $homeAddress = [];
        if (
            isset($data['contactDetails']) &&
            isset($data['contactDetails']['contactType']['id']) &&
            $data['contactDetails']['contactType']['id'] == ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
            ) {

            $tmDetails['contactedTypeId'] =
               isset($data['contactDetails']['id']) ? $data['contactDetails']['id'] : '';
            $tmDetails['contactedTypeVersion'] =
               isset($data['contactDetails']['version']) ? $data['contactDetails']['version'] : '';
            $tmDetails['emailAddress'] =
               isset($data['contactDetails']['emailAddress']) ? $data['contactDetails']['emailAddress'] : '';

            if (isset($data['contactDetails']['person'])) {
                $person = $data['contactDetails']['person'];
                $tmDetails['personId'] = isset($person['id']) ? $person['id'] : '';
                $tmDetails['personVersion'] = isset($person['id']) ? $person['id'] : '';
                $tmDetails['title'] = isset($person['title']) ? $person['title'] : '';
                $tmDetails['firstName'] = isset($person['forename']) ? $person['forename'] : '';
                $tmDetails['lastName'] = isset($person['familyName']) ? $person['familyName'] : '';
                $tmDetails['birthPlace'] = isset($person['birthPlace']) ? $person['birthPlace'] : '';
                $tmDetails['birthDate'] = isset($person['birthDate']) ? $person['birthDate'] : '';
            }

            if (isset($data['contactDetails']['address'])) {
                $address = $data['contactDetails']['address'];
                $homeAddress['id'] = isset($address['id']) ? $address['id'] : '';
                $homeAddress['version'] = isset($address['version']) ? $address['version'] : '';
                $homeAddress['addressLine1'] = isset($address['addressLine1']) ? $address['addressLine1'] : '';
                $homeAddress['addressLine2'] = isset($address['addressLine2']) ? $address['addressLine2'] : '';
                $homeAddress['addressLine3'] = isset($address['addressLine3']) ? $address['addressLine3'] : '';
                $homeAddress['addressLine4'] = isset($address['addressLine4']) ? $address['addressLine4'] : '';
                $homeAddress['town'] = isset($address['town']) ? $address['town'] : '';
                $homeAddress['postcode'] = isset($address['postcode']) ? $address['postcode'] : '';
            }
        }
        
        $formattedData = [
            'transport-manager-details' => $tmDetails,
            'home-address' => $homeAddress
        ];
        return $formattedData;
    }
    
    /**
     * Save transport manager
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        $addressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['home-address']);
        
        $addressId = isset($addressSaved['id']) ? $addressSaved['id'] : $data['home-address']['id'];

        $person = [
            'id' => $data['transport-manager-details']['personId'],
            'version' => $data['transport-manager-details']['personVersion'],
            'title' => $data['transport-manager-details']['title'],
            'forename' => $data['transport-manager-details']['lastName'],
            'familyName' => $data['transport-manager-details']['firstName'],
            'birthDate' => sprintf(
                '%s-%s-%s',
                $data['transport-manager-details']['birthDate']['year'],
                $data['transport-manager-details']['birthDate']['month'],
                $data['transport-manager-details']['birthDate']['day']
            ),
            'birthPlace' => $data['transport-manager-details']['birthPlace']
        ];
        $personSaved = $this->getServiceLocator()->get('Entity\Person')->save($person);
        $personId = isset($personSaved['id']) ? $personSaved['id'] : $data['personId'];

        $contactDetails = [
            'id' => $data['transport-manager-details']['contactDetailsId'],
            'version' => $data['transport-manager-details']['contactDetailsVersion'],
            'person' => $personId,
            'address' => $addressId,
            'emailAddress' => isset($data['transport-manager-details']['emailAddress']) ?
                $data['transport-manager-details']['emailAddress'] : '',
            'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
        ];
        $contactDetailsSaved = $this->getServiceLocator()->get('Entity\ContactDetails')->save($contactDetails);
        $contactDetailsId = isset($contactDetailsSaved['id']) ?
            $contactDetailsSaved['id'] : $data['transport-manager-details']['contactDetailsId'];
        
        $userField = isset($data['id']) ? 'modifiedBy' : 'createdBy';
                
        $transportManager = [
            'tmType' => $data['transport-manager-details']['type'],
            'contactDetail' => $contactDetailsId,
            $userField => $this->getLoggedInUser()
        ];
        $this->getServiceLocator()->get('Entity\TransportManager')->save($transportManager);

        $this->flashMessenger()->addSuccessMessage('The transport manager has been updated successfully');   
    }
}
