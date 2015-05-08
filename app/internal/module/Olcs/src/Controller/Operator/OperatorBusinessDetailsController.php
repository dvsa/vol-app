<?php

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Common\Service\Entity\OrganisationEntityService;
use Common\Service\Entity\AddressEntityService;

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorBusinessDetailsController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'business_details';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_profile';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $operator = $this->params()->fromRoute('organisation');
        $this->loadScripts(['operator-profile']);
        $post = $this->params()->fromPost();
        $validateAndSave = true;

        if ($this->isButtonPressed('cancel')) {
            // user pressed cancel button in edit form
            if ($operator) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirectToRoute('operator/business-details', ['organisation' => $operator]);
            } else {
                // user pressed cancel button in add form
                return $this->redirectToRoute('operators/operators-params');
            }
        }

        if ($this->getRequest()->isPost()) {
            // if this is post always take organisation type from parameters
            $operatorType = $post['operator-business-type']['type'];
        } elseif (!$operator) {
            // we are in add mode, this is default organisation type
            $operatorType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        } else {
            // we are in edit mode, need to fetch original data
            $operatorType = $this->getOrganistionType($operator);
        }

        $form = $this->makeFormAlterations($operatorType, $this->getForm('Operator'));
        // don't need validate form and save data if user just changed organisation's type
        if (isset($post['operator-business-type']['refresh'])) {
            // non-js version of form
            unset($post['operator-business-type']['refresh']);
            $validateAndSave = false;
        }

        /* if we are in edit mode and just changed the business type or
         * this is not a post we need to populate form with
         * original values, otherwise we use POST values
         */
        if ($operator && (!$validateAndSave || !$this->getRequest()->isPost())) {
            $originalData = $this->prepareOriginalData($operator);
            if (!$validateAndSave) {
                $originalData['type'] = $operatorType;
            }
            $form = $this->setDataOperatorForm($form, $originalData);
        } else {
            $form->setData($post);
        }

        // process company lookup
        if (isset($post['operator-details']['companyNumber']['submit_lookup_company'])) {
            $this->getServiceLocator()->get('Helper\Form')
                ->processCompanyNumberLookupForm($form, $post, 'operator-details', 'registeredAddress');
            $validateAndSave = false;
        }

        if ($this->getRequest()->isPost() && $validateAndSave) {
            if (!$this->getEnabledCsrf()) {
                $this->getServiceLocator()->get('Helper\Form')->remove($form, 'csrf');
            }
            if ($form->isValid()) {

                $action = $operator ? 'edit' : 'add';
                $this->saveForm($form, $action);

                // we need to process redirect and catch flashMessenger messages if available
                if ($this->getResponse()->getStatusCode() == 302) {
                    return $this->getResponse();
                }
                // need to reload form, to update version and all other fields, because we are still on the same page
                $form = $this->setDataOperatorForm($form, $this->prepareOriginalData($operator));
            }
        }

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('partials/form');
        return $this->renderView($view);
    }

    /**
     * Fetch original data for organisation
     *
     * @param int $operator
     * @return array
     */
    private function prepareOriginalData($operator)
    {
        $organisationEntityService = $this->getServiceLocator()->get('Entity\Organisation');

        $fetchedData = $organisationEntityService->getBusinessDetailsData($operator);
        $data = [
            'id' => $operator,
            'version' => $fetchedData['version'],
            'name' => $fetchedData['name'],
            'companyNumber' => $fetchedData['companyOrLlpNo'],
            'isIrfo' => $fetchedData['isIrfo'],
            'type' => $fetchedData['type']['id']
        ];
        $person = $this->getServiceLocator()->get('Entity\Person')->getFirstForOrganisation($data['id']);
        if (count($person)) {
            $data['firstName'] = isset($person['forename']) ? $person['forename'] : '';
            $data['lastName'] = isset($person['familyName']) ? $person['familyName'] : '';
            $data['personId'] = isset($person['id']) ? $person['id'] : '';
            $data['personVersion'] = isset($person['version']) ? $person['version'] : '';
        }
        $data['registeredAddress'] = $this->extractRegisteredAddress($fetchedData);

        $natureOfBusiness = $organisationEntityService->getNatureOfBusinessesForSelect($data['id']);
        $data['natureOfBusinesses'] = $natureOfBusiness;
        return $data;
    }

    /**
     * Set data for operator form
     *
     * @param Zend\Form\Form $form
     * @param array $data
     * @return Zend\Form\Form
     */
    private function setDataOperatorForm($form, $data = [])
    {
        if ($form) {
            $operatorDetails = [
                'id' => $data['id'],
                'version' => $data['version'],
                'natureOfBusinesses' => $data['natureOfBusinesses'],
                'isIrfo' => $data['isIrfo']
            ];
            $registeredAddress = [];
            switch ($data['type']) {
                case OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY:
                case OrganisationEntityService::ORG_TYPE_LLP:
                    $operatorDetails['name'] = $data['name'];
                    $operatorDetails['companyNumber']['company_number'] = $data['companyNumber'];
                    $registeredAddress = $data['registeredAddress'];
                    break;
                case OrganisationEntityService::ORG_TYPE_SOLE_TRADER:
                    $operatorDetails['firstName'] = isset($data['firstName']) ? $data['firstName'] : '';
                    $operatorDetails['lastName'] = isset($data['lastName']) ? $data['lastName'] : '';
                    $operatorDetails['personId'] = isset($data['personId']) ? $data['personId'] : '';
                    $operatorDetails['personVersion'] = isset($data['personVersion']) ? $data['personVersion'] : '';
                    break;
                case OrganisationEntityService::ORG_TYPE_PARTNERSHIP:
                case OrganisationEntityService::ORG_TYPE_OTHER:
                case OrganisationEntityService::ORG_TYPE_IRFO:
                    $operatorDetails['name'] = $data['name'];
                    break;
            }
            $formData = [
                'operator-business-type' => ['type' => $data['type']],
                'operator-details' => $operatorDetails,
                'registeredAddress' => $registeredAddress
            ];
            $form->setData($formData);
        }
        return $form;
    }

    /**
     * Save form
     *
     * @param Zend\Form\Form $form
     * @param strring $action
     * @return mixed
     */
    private function saveForm($form, $action)
    {
        $data = $form->getData();

        if ($action == 'edit') {
            $message = 'The operator has been updated successfully';
        } else {
            $message = 'The operator has been created successfully';
        }

        $params = $data['operator-details'];
        $params['type'] = $data['operator-business-type']['type'];

        if (isset($data['operator-details']['companyNumber']['company_number'])) {
            $params['companyOrLLpNo'] = $data['operator-details']['companyNumber']['company_number'];
        }

        if ($params['type'] == OrganisationEntityService::ORG_TYPE_SOLE_TRADER) {
            $params['name'] = $params['firstName'] . ' ' . $params['lastName'];
        }

        if ($params['type'] == OrganisationEntityService::ORG_TYPE_IRFO) {
            $params['isIrfo'] = 'Y';
        }

        $saved = $this->getServiceLocator()->get('Entity\Organisation')->save($params);
        $orgId = isset($saved['id']) ? $saved['id'] : $params['id'];

        $registeredAddressTypes = [
            OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY,
            OrganisationEntityService::ORG_TYPE_LLP
        ];

        if (in_array($params['type'], $registeredAddressTypes)) {
            $contactDetailsId = $this->saveRegisteredAddress($data['registeredAddress']);
            if (is_int($contactDetailsId)) {
                $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
                    $orgId,
                    ['contactDetails' => $contactDetailsId]
                );
            }
        }

        if ($params['type'] == OrganisationEntityService::ORG_TYPE_SOLE_TRADER) {
            $this->savePerson($orgId, $data['operator-details']);
        }

        $this->flashMessenger()->addSuccessMessage($message);

        return $this->redirectToRoute('operator/business-details', ['organisation' => $orgId]);
    }

    /**
     * Save person for sole trader
     *
     * @param int $id
     * @param array $data
     */
    private function savePerson($id, $data)
    {
        $person = [
            'id' => $data['personId'],
            'version'=> $data['personVersion'],
            'forename' => $data['firstName'],
            'familyName' => $data['lastName']
        ];

        $saved = $this->getServiceLocator()->get('Entity\Person')->save($person);
        if (!isset($data['personId']) || empty($data['personId'])) {
            $organisationPerson = [
                'organisation' => $id,
                'person' => $saved['id']
            ];
            $this->getServiceLocator()->get('Entity\OrganisationPerson')->save($organisationPerson);
        }
    }

    /**
     * Make form alterations
     *
     * @param string $businessType
     * @param Zend\Form\Form $form
     * @return form
     */
    private function makeFormAlterations($businessType, $form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        switch ($businessType) {
            case OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY:
            case OrganisationEntityService::ORG_TYPE_LLP:
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                break;
            case OrganisationEntityService::ORG_TYPE_SOLE_TRADER:
                $formHelper->remove($form, 'operator-details->companyNumber');
                $formHelper->remove($form, 'operator-details->name');
                $formHelper->remove($form, 'registeredAddress');
                break;
            case OrganisationEntityService::ORG_TYPE_PARTNERSHIP:
            case OrganisationEntityService::ORG_TYPE_OTHER:
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                $formHelper->remove($form, 'registeredAddress');
                $formHelper->remove($form, 'operator-details->companyNumber');
                break;
            case OrganisationEntityService::ORG_TYPE_IRFO:
                $formHelper->remove($form, 'operator-details->companyNumber');
                $formHelper->remove($form, 'operator-details->natureOfBusinesses');
                $formHelper->remove($form, 'operator-details->information');
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                $formHelper->remove($form, 'operator-details->isIrfo');
                $formHelper->remove($form, 'registeredAddress');
                break;
        }
        return $form;
    }

    /**
     * Extract registered address
     *
     * @param array $data
     * @return array
     */
    private function extractRegisteredAddress($data)
    {
        if (isset($data['contactDetails']['address'])) {
            return $data['contactDetails']['address'];
        }

        return [];
    }

    /**
     * Get organisation type
     *
     * @param int $operator
     * @return string
     */
    private function getOrganistionType($operator)
    {
        $data = $this->getServiceLocator()->get('Entity\Organisation')->getBusinessDetailsData($operator);
        return isset($data['type']['id']) ? $data['type']['id'] : '';
    }

    /**
     * Save the organisations registered address
     *
     * @param array $address
     */
    protected function saveRegisteredAddress($address)
    {
        $saved = $this->getServiceLocator()->get('Entity\Address')->save($address);

        // If we didn't have an address id, then we need to link it to the organisation
        if (!isset($address['id']) || empty($address['id'])) {
            $contactDetailsData = array(
                'address' => $saved['id'],
                'contactType' => AddressEntityService::CONTACT_TYPE_REGISTERED_ADDRESS
            );

            $saved = $this->getServiceLocator()->get('Entity\ContactDetails')->save($contactDetailsData);
            return $saved['id'];
        }
    }
}
