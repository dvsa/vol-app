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
        $view->setTemplate('partials/form');
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
            isset($data['homeCd']['contactType']['id']) &&
            $data['homeCd']['contactType']['id'] == ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
            ) {

            $tmDetails['homeCdId'] =
               isset($data['homeCd']['id']) ? $data['homeCd']['id'] : '';
            $tmDetails['homeCdVersion'] =
               isset($data['homeCd']['version']) ? $data['homeCd']['version'] : '';
            $tmDetails['emailAddress'] =
               isset($data['homeCd']['emailAddress']) ? $data['homeCd']['emailAddress'] : '';

            if (isset($data['homeCd']['person'])) {
                $person = $data['homeCd']['person'];
                $tmDetails['personId'] = isset($person['id']) ? $person['id'] : '';
                $tmDetails['personVersion'] = isset($person['id']) ? $person['version'] : '';
                $tmDetails['title'] = isset($person['title']) ? $person['title'] : '';
                $tmDetails['firstName'] = isset($person['forename']) ? $person['forename'] : '';
                $tmDetails['lastName'] = isset($person['familyName']) ? $person['familyName'] : '';
                $tmDetails['birthPlace'] = isset($person['birthPlace']) ? $person['birthPlace'] : '';
                $tmDetails['birthDate'] = isset($person['birthDate']) ? $person['birthDate'] : '';
            }

            if (isset($data['homeCd']['address'])) {
                $address = $data['homeCd']['address'];
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
        $workAddress = [];
        if (
            isset($data['workCd']['contactType']['id']) &&
            $data['workCd']['contactType']['id'] == ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
            ) {
            $tmDetails['workCdId'] =
               isset($data['workCd']['id']) ? $data['workCd']['id'] : '';
            $tmDetails['workCdVersion'] =
               isset($data['workCd']['version']) ? $data['workCd']['version'] : '';

            if (isset($data['workCd']['address'])) {
                $address = $data['workCd']['address'];
                $workAddress['id'] = isset($address['id']) ? $address['id'] : '';
                $workAddress['version'] = isset($address['version']) ? $address['version'] : '';
                $workAddress['addressLine1'] = isset($address['addressLine1']) ? $address['addressLine1'] : '';
                $workAddress['addressLine2'] = isset($address['addressLine2']) ? $address['addressLine2'] : '';
                $workAddress['addressLine3'] = isset($address['addressLine3']) ? $address['addressLine3'] : '';
                $workAddress['addressLine4'] = isset($address['addressLine4']) ? $address['addressLine4'] : '';
                $workAddress['town'] = isset($address['town']) ? $address['town'] : '';
                $workAddress['postcode'] = isset($address['postcode']) ? $address['postcode'] : '';
            }
        }

        $formattedData = [
            'transport-manager-details' => $tmDetails,
            'home-address' => $homeAddress,
            'work-address' => $workAddress
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
        $homeAddressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['home-address']);
        $homeAddressId = isset($homeAddressSaved['id']) ? $homeAddressSaved['id'] : $data['home-address']['id'];

        $workAddressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['work-address']);
        $workAddressId = isset($workAddressSaved['id']) ? $workAddressSaved['id'] : $data['work-address']['id'];

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

        $homeCd = [
            'id' => $data['transport-manager-details']['homeCdId'],
            'version' => $data['transport-manager-details']['homeCdVersion'],
            'person' => $personId,
            'address' => $homeAddressId,
            'emailAddress' => isset($data['transport-manager-details']['emailAddress']) ?
                $data['transport-manager-details']['emailAddress'] : '',
            'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
        ];
        $homeCdSaved = $this->getServiceLocator()->get('Entity\ContactDetails')->save($homeCd);
        $homeCdId = isset($homeCdSaved['id']) ?
            $homeCdSaved['id'] : $data['transport-manager-details']['homeCdId'];

        $workCd = [
            'id' => $data['transport-manager-details']['workCdId'],
            'version' => $data['transport-manager-details']['workCdVersion'],
            'address' => $workAddressId,
            'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
        ];
        $workCdSaved = $this->getServiceLocator()->get('Entity\ContactDetails')->save($workCd);
        $workCdId = isset($workCdSaved['id']) ?
            $workCdSaved['id'] : $data['transport-manager-details']['workCdId'];

        $userField = ($action == 'edit') ? 'modifiedBy' : 'createdBy';

        $transportManager = [
            'id' => $data['transport-manager-details']['id'],
            'version' => $data['transport-manager-details']['version'],
            'tmType' => $data['transport-manager-details']['type'],
            'tmStatus' => isset($data['transport-manager-details']['status']) &&
                !empty($data['transport-manager-details']['status']) ?
                $data['transport-manager-details']['status'] :
                TransportManagerEntityService::TRANSPORT_MANAGER_STATUS_ACTIVE,
            'homeCd' => $homeCdId,
            'workCd' => $workCdId,
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
