<?php

/**
 * ConditionUndertaking controller
 *
 * Adds/edits a conditionUndertaking
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\CrudInterface;

/**
 * ConditionUndertaking controller
 *
 * Adds/edits a conditionUndertaking
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingController extends FormActionController implements CrudInterface
{
    public function redirectToIndex()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'type', 'id'));

        return $this->redirect()->toRoute(
            'case_conditions_undertakings', array(
                'licence' => $routeParams['licence'],
                'case' => $routeParams['case']
            )
        );
    }

    public function deleteAction()
    {
        //
    }

    public function indexAction()
    {
        //
    }



    /**
     * Method to generate the add form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'type'));

        // check valid case exists
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        // check for cancel button
        if (null !== $this->params()->fromPost('cancel-conditionUndertaking')) {
            return $this->redirect()->toRoute(
                'case_conditions_undertakings', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }

        $this->determineBreadcrumbs($routeParams);

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'isDraft' => 0,
            'vosaCase' => $routeParams['case'],
            'licence' => $routeParams['licence']
        );

        $form = $this->generateFormWithData(
            'condition-undertaking-form', 'processConditionUndertaking', $data
        );
        // set the OC address list and label for conditionType
        $form = $this->configureFormForConditionType(
            $form,
            $routeParams['licence'],
            $routeParams['type']
        );

        $view = new ViewModel(
            array(
            'form' => $form,
            'params' => array(
                'pageTitle' => 'add-' . $routeParams['type'],
                'pageSubTitle' => 'subtitle-' . $routeParams['type'] . '-text'
            )
            )
        );
        $view->setTemplate('conditionUndertaking/form');
        return $view;
    }

    /**
     * Method to generate the edit form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'type', 'id'));

        // check valid case exists
        $results = $this->makeRestCall(
            'VosaCase',
            'GET',
            array('id' => $routeParams['case'])
        );
        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        // check for cancel button
        if (null !== $this->params()->fromPost('cancel-conditionUndertaking')) {
            return $this->redirect()->toRoute(
                'case_conditions_undertakings', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }

        $this->determineBreadcrumbs($routeParams);

        $bundle = $this->getConditionUndertakingBundle();

        $data['condition-undertaking'] = $this->makeRestCall(
            'ConditionUndertaking',
            'GET',
            array('id' => $routeParams['id'], 'bundle' => json_encode($bundle))
        );

        // assign data as required by the form
        $data['condition-undertaking']['vosaCase'] = $data['condition-undertaking']['vosaCase']['id'];
        $data['condition-undertaking']['licence'] = $routeParams['licence'];
        $data['condition-undertaking']['isDraft'] = $data['condition-undertaking']['isDraft'] ? 1 : 0;

        $data = $this->determineFormAttachedTo($data);

        $form = $this->generateFormWithData(
            'condition-undertaking-form', 'processConditionUndertaking', $data, true
        );

        // set the OC address list and label for conditionType
        $form = $this->configureFormForConditionType(
            $form,
            $routeParams['licence'],
            $routeParams['type']
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'params' => array(
                    'pageTitle' => 'edit-' . $routeParams['type'],
                    'pageSubTitle' => 'subtitle-' . $routeParams['type'] . '-text'
                )
            )
        );
        $view->setTemplate('conditionUndertaking/form');
        return $view;
    }

    /**
     * Method to process the CRUD form submission. Calls process Edit or Add
     *
     * @param array $data
     */
    public function processConditionUndertaking($data)
    {

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        $data = $this->determineSavingAttachedTo($data);

        $data['condition-undertaking']['lastUpdatedOn'] = date('d-m-Y h:i:s');
        $data['condition-undertaking']['createdBy'] = $this->getLoggedInUser();

        if (strtolower($routeParams['action']) == 'edit') {

            $result = $this->processEdit($data['condition-undertaking'], 'ConditionUndertaking');

        } else {
            // configure condition-undertaking data
            unset($data['condition-undertaking']['version']);
            unset($data['condition-undertaking']['id']);
            $data['condition-undertaking']['createdOn'] = date('d-m-Y h:i:s');

            $result = $this->processAdd($data['condition-undertaking'], 'ConditionUndertaking');

        }

        return $this->redirect()->toRoute(
            'case_conditions_undertakings',
            array(
                'case' => $routeParams['case'], 'licence' => $routeParams['licence']
            )
        );
    }

    /**
     * Method to extract all Operating Centre Addresses for a given licence
     * and reformat into array suitable for select options
     *
     * @param integer $licenceId
     * @return array address_id => [address details]
     */
    public function getOcAddressByLicence($licenceId)
    {
        $operatingCentreAddressBundle = $this->getOcAddressBundle();
        $result = $this->makeRestCall(
            'OperatingCentre',
            'GET',
            array(
                'licence' => $licenceId,
                'bundle' => json_encode($operatingCentreAddressBundle)
            )
        );

        if ($result['Count']) {
            foreach ($result['Results'] as $oc) {
                $address = $oc['address'];
                $operatingCentreAddresses[$oc['id']] =
                    $address['addressLine1'] . ', ' .
                    $address['addressLine2'] . ', ' .
                    $address['addressLine3'] . ', ' .
                    $address['addressLine4'] . ', ' .
                    $address['postcode'] . ', ' .
                    $address['country'];
            }
        }
        // set up the group options required by Zend
        $options = array(
            'Licence' => array(
                'label' => 'Licence',
                'options' => array(
                    'Licence' => 'Licence ' . $licenceId
                ),
            ),
            'OC' => array(
                'label' => 'OC Address',
                'options' => $operatingCentreAddresses
            )
        );

        return $options;
    }

    /**
     * Method to return the bundle required for getting conditionUndertakings and related
     * entities from the database.
     *
     * @return array
     */
    public function getConditionUndertakingBundle()
    {
        return array(

            'children' => array(
                'vosaCase' => array(
                    'properties' => array(
                        'id',
                        'operating_centre_id',
                        'licence_id'
                    ),
                ),
                'licence' => array(
                    'properties' => array(
                        'id',
                    ),
                ),
                'operatingCentre' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
            )
        );
    }

    /**
     * Method to return the bundle required for getting all operating centre
     * addresses for a given licence
     *
     * @return array
     */
    public function getOcAddressBundle()
    {
        return array(
            'properties' => array(
                'id',
                'address'
            ),
            'children' => array(
                'address' => array(
                    'properties' => array(
                        'id',
                        'addressLine1',
                        'addressLine2',
                        'addressLine3',
                        'addressLine4',
                        'paon_desc',
                        'saon_desc',
                        'street',
                        'locality',
                        'postcode',
                        'country'
                    )
                )
            )
        );
    }

    /**
     * The attachedTo dropdown has values of either 'licence' or an OC id
     * However what is stored is either 'OC' or 'Licence' so this method
     * sets the value to the OC id in preparation for generating the edit form
     *
     * @param array $data
     * @return array
     */
    private function determineFormAttachedTo($data)
    {
        // for form
        if ($data['condition-undertaking']['attachedTo'] != 'Licence') {
            $data['condition-undertaking']['attachedTo'] =
                isset($data['condition-undertaking']['operatingCentre']['id']) ?
                    $data['condition-undertaking']['operatingCentre']['id'] : '';
        }

        return $data;
    }

     /**
     * The attachedTo dropdown has values of either 'licence' or an OC id
     * However what is stored is either 'OC' or 'Licence' so this method
     * sets the value from OC id to the value 'OC' or 'Licence'
     * in preparation for saving the data
     *
     * @param array $data
     * @return array
     */
    private function determineSavingAttachedTo($data)
    {
        if (strtolower($data['condition-undertaking']['attachedTo']) !== 'licence') {
            $data['condition-undertaking']['operatingCentre'] =
                $data['condition-undertaking']['attachedTo'];
            $data['condition-undertaking']['attachedTo'] = 'OC';
        } else {
            $data['condition-undertaking']['operatingCentre'] = null;
            $data['condition-undertaking']['attachedTo'] = 'Licence';
        }

        return $data;

    }

    /**
     * Sets up the breadcrumbs
     *
     * @param type $routeParams
     */
    private function determineBreadcrumbs($routeParams)
    {

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array(
                    'licence' => $routeParams['licence']
                ),
                'case_conditions_undertakings' => array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            )
        );
    }

    /**
     * Sets the notes field label accoring to the type of condition.
     * i.e. Undertaking or Condition
     * Also extracts the Operating Centre addresses for the licence and sets
     * up the group options for the attachedTo drop down
     *
     * @param \Zend\Form\Form $form
     * @param integer $licenceId
     * @param string $type
     * @return \Zend\Form\Form $form
     */
    public function configureFormForConditionType($form, $licenceId, $type)
    {

        $ocAddressList = $this->getOcAddressByLicence($licenceId);

        // set form dependent aspects
        $form->get('condition-undertaking')->get('notes')->setLabel(ucfirst($type));
        $form->get('condition-undertaking')
                ->get('attachedTo')
                ->setValueOptions($ocAddressList);

        return $form;

    }
}
