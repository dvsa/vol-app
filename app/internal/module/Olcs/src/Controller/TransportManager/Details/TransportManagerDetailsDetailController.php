<?php

/**
 * Transport Manager Details Detail Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Common\Service\Entity\ContactDetailsEntityService;
use Common\Service\Entity\TransportManagerEntityService;

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
     * @var bool
     */
    protected $saved = false;

    /**
     * Index action
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $tmId = $this->params()->fromRoute('transportManager');

        if ($this->isButtonPressed('cancel')) {
            if ($tmId) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirectToRoute('transport-manager/details', ['transportManager' => $tmId]);
            } else {
                return $this->redirectToRoute('operators/operators-params');
            }
        }

        $form = $this->getForm('TransportManager');
        if (!$this->getRequest()->isPost() && $tmId) {
            // need to populate form with original data if we are in the edit mode or user pressed cancel
            $form = $this->populateFormWithData($form, $tmId);
        }

        $this->formPost($form, 'processSave');
        if ($this->getResponse()->getStatusCode() == 302) {
            return $this->getResponse();
        }

        if ($this->saved) {
            // need to re-populate form again, if data was saved, to avoid version conflict
            $form = $this->populateFormWithData($form, $tmId);
        }

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('transport-manager/details/tm-details');
        return $this->renderView($view);
    }

    /**
     * Populate form with original data
     *
     * @param Zend\Form\Form
     * @param int $tmId
     * @return Zend\Form\Form
     */
    protected function populateFormWithData($form, $tmId)
    {
        $tmData = $this->getServiceLocator()->get('Entity\TransportManager')->getTmDetails($tmId);
        $data = $this->formatDataForForm($tmData, $tmId);
        $form->setData($data);
        return $form;
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
            'type' => isset($data['tmType']['id']) ? $data['tmType']['id'] : '',
            'status' => isset($data['tmStatus']['id']) ? $data['tmStatus']['id'] : ''
        ];
        $homeAddress = [];
        if (
            isset($data['contactDetails']) &&
            isset($data['contactDetails']['contactType']['id']) &&
            $data['contactDetails']['contactType']['id'] == ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
            ) {

            $tmDetails['contactDetailsId'] =
               isset($data['contactDetails']['id']) ? $data['contactDetails']['id'] : '';
            $tmDetails['contactDetailsVersion'] =
               isset($data['contactDetails']['version']) ? $data['contactDetails']['version'] : '';
            $tmDetails['emailAddress'] =
               isset($data['contactDetails']['emailAddress']) ? $data['contactDetails']['emailAddress'] : '';

            if (isset($data['contactDetails']['person'])) {
                $person = $data['contactDetails']['person'];
                $tmDetails['personId'] = isset($person['id']) ? $person['id'] : '';
                $tmDetails['personVersion'] = isset($person['id']) ? $person['version'] : '';
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
     * @return mixed
     */
    protected function processSave($data)
    {
        $action = isset($data['transport-manager-details']['id']) && !empty($data['transport-manager-details']['id']) ?
            'edit' : 'add';
        $addressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['home-address']);
        $addressId = isset($addressSaved['id']) ? $addressSaved['id'] : $data['home-address']['id'];

        $person = [
            'id' => $data['transport-manager-details']['personId'],
            'version' => $data['transport-manager-details']['personVersion'],
            'title' => $data['transport-manager-details']['title'],
            'forename' => $data['transport-manager-details']['firstName'],
            'familyName' => $data['transport-manager-details']['lastName'],
            'birthDate' => $data['transport-manager-details']['birthDate'],
            'birthPlace' => $data['transport-manager-details']['birthPlace']
        ];
        $personSaved = $this->getServiceLocator()->get('Entity\Person')->save($person);
        $personId = isset($personSaved['id']) ? $personSaved['id'] : $data['transport-manager-details']['personId'];

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

        $userField = ($action == 'edit') ? 'modifiedBy' : 'createdBy';

        $transportManager = [
            'id' => $data['transport-manager-details']['id'],
            'version' => $data['transport-manager-details']['version'],
            'tmType' => $data['transport-manager-details']['type'],
            'tmStatus' => isset($data['transport-manager-details']['status']) &&
                !empty($data['transport-manager-details']['status']) ?
                $data['transport-manager-details']['status'] :
                TransportManagerEntityService::TRANSPORT_MANAGER_STATUS_ACTIVE,
            'contactDetails' => $contactDetailsId,
            $userField => $this->getLoggedInUser()
        ];
        $tmSaved = $this->getServiceLocator()->get('Entity\TransportManager')->save($transportManager);

        $this->saved = true;
        if ($action == 'add') {
            $message = 'The Transport Manager has been created successfully';
        } else {
            $message = 'The Transport Manager has been updated successfully';
        }
        $this->flashMessenger()->addSuccessMessage($message);
        if ($action == 'add') {
            $this->redirectToRoute('transport-manager/details/details', ['transportManager' => $tmSaved['id']]);
        }
    }
}
