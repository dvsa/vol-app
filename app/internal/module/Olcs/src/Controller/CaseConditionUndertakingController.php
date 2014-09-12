<?php

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Olcs\Controller\Traits\DeleteActionTrait;
use Zend\View\Model\ViewModel;
use Common\Service\Table\Formatter\Address;

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class CaseConditionUndertakingController extends CaseController
{
    use DeleteActionTrait;

    const CONDITION_TYPE_CONDITION = 'cdt_con';
    const CONDITION_TYPE_UNDERTAKING = 'cdt_und';

    const ATTACHED_TO_LICENCE = 'cat_lic';
    const ATTACHED_TO_OPERATING_CENTRE = 'cat_oc';

    private $tables = array(
        self::CONDITION_TYPE_CONDITION => 'conditions',
        self::CONDITION_TYPE_UNDERTAKING => 'undertakings'
    );

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return 'ConditionUndertaking';
    }

    /**
     * Main index action responsible for generating the main landing page for
     * complaints.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');

        $table = $this->params()->fromPost('table');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        if (!empty($table)) {
            // checks for CRUD and redirects as required
            $this->checkForCrudAction($table, array('case' => $caseId, 'licence' => $licenceId), 'id');
        }

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'conditions-undertakings';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);

        $conditionsTable = $this->generateConditionTable($caseId);
        $undertakingsTable = $this->generateUndertakingTable($caseId);

        $view->setVariables(
            [
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'conditionsTable' => $conditionsTable,
            'undertakingsTable' => $undertakingsTable,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
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
        $results = $this->makeRestCall('Cases', 'GET', array('id' => $routeParams['case']));
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

        $conditionType = $this->getConditionTypeFromType($routeParams['type']);

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => $conditionType,
            'isDraft' => 0,
            'case' => $routeParams['case'],
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

    private function getConditionTypeFromType($type)
    {
        switch ($type) {
            case 'condition':
                return self::CONDITION_TYPE_CONDITION;
            case 'undertaking':
                return self::CONDITION_TYPE_UNDERTAKING;
        }

        return null;
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
            'Cases',
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

        $data['condition-undertaking'] = $this->makeRestCall(
            'ConditionUndertaking',
            'GET',
            array('id' => $routeParams['id']),
            $this->getConditionUndertakingBundleForUpdate()
        );

        // assign data as required by the form
        $data['condition-undertaking']['case'] = $data['condition-undertaking']['case']['id'];
        $data['condition-undertaking']['licence'] = $routeParams['licence'];
        $data['condition-undertaking']['isDraft'] = $data['condition-undertaking']['isDraft'] ? 1 : 0;
        $data['condition-undertaking']['conditionType'] = $data['condition-undertaking']['conditionType']['id'];
        $data['condition-undertaking']['addedVia'] = isset($data['condition-undertaking']['addedVia']['id'])
            ? $data['condition-undertaking']['addedVia']['id'] : null;
        $data['condition-undertaking']['attachedTo'] = isset($data['condition-undertaking']['attachedTo']['id'])
            ? $data['condition-undertaking']['attachedTo']['id'] : null;

        $data = $this->determineFormAttachedTo($data);

        $form = $this->generateFormWithData('condition-undertaking-form', 'processConditionUndertaking', $data);

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
     * Method to generate the conditions table
     *
     * @param id $caseId
     * @return string
     */
    public function generateConditionTable($caseId)
    {
        return $this->generateTable($caseId, 'cdt_con');
    }

    /**
     * Method to generate the undertakings table
     *
     * @param id $caseId
     * @return string
     */
    public function generateUndertakingTable($caseId)
    {
        return $this->generateTable($caseId, 'cdt_und');
    }

    /**
     * Moved duplicate generateTable logic to it's own method
     *
     * @param int $caseId
     * @param string $which
     * @return string
     */
    private function generateTable($caseId, $which)
    {
        $results = $this->makeRestCall(
            'Cases',
            'GET',
            array('id' => $caseId),
            $this->getConditionUndertakingBundle($which)
        );

        $conditionUndertakings = $results['conditionUndertakings'];

        foreach ($conditionUndertakings as &$result) {
            $result['caseId'] = $caseId;
            $result['operatingCentreAddress'] =
                isset($result['operatingCentre']['address']) ? $result['operatingCentre']['address'] : null;
        }


        return $this->getTable($this->tables[$which], $conditionUndertakings);
    }

    /**
     * Method to return the bundle required for getting conditionUndertakings and related
     * entities from the database.
     *
     * @return array
     */
    public function getConditionUndertakingBundleForUpdate()
    {
        return array(
            'children' => array(
                'conditionType' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'addedVia' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'attachedTo' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'licence' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'operatingCentre' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );
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
        $result = $this->makeRestCall(
            'OperatingCentre',
            'GET',
            array('licence' => $licenceId),
            $this->getOcAddressBundle()
        );

        if ($result['Count']) {
            foreach ($result['Results'] as $oc) {
                $operatingCentreAddresses[$oc['id']] = Address::format($oc['address']);
            }
        }
        // set up the group options required by Zend
        $options = array(
            'Licence' => array(
                'label' => 'Licence',
                'options' => array(
                    self::ATTACHED_TO_LICENCE => 'Licence ' . $licenceId
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
                        'town',
                        'postcode'
                    ),
                    'children' => array(
                        'countryCode' => array(
                            'properties' => array(
                                'id'
                            )
                        )
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
        if ($data['condition-undertaking']['attachedTo'] != self::ATTACHED_TO_LICENCE) {
            $data['condition-undertaking']['attachedTo'] =
                isset($data['condition-undertaking']['operatingCentre']['id'])
                    ? $data['condition-undertaking']['operatingCentre']['id'] : '';
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
        if (strtolower($data['condition-undertaking']['attachedTo']) !== self::ATTACHED_TO_LICENCE) {
            $data['condition-undertaking']['operatingCentre'] = $data['condition-undertaking']['attachedTo'];
            $data['condition-undertaking']['attachedTo'] = self::ATTACHED_TO_OPERATING_CENTRE;
        } else {
            $data['condition-undertaking']['operatingCentre'] = null;
            $data['condition-undertaking']['attachedTo'] = self::ATTACHED_TO_LICENCE;
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
        $form->get('condition-undertaking')
            ->get('notes')
            ->setLabel(ucfirst($type));

        $form->get('condition-undertaking')
            ->get('attachedTo')
            ->setValueOptions($ocAddressList);

        return $form;

    }

    /**
     * Method to return the bundle required for conditionundertakings
     * by conditionType
     *
     * @todo remove address lines when address entity finalised
     * @return array
     */
    private function getConditionUndertakingBundle($conditionType)
    {

        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'conditionUndertakings' => array(
                    'criteria' => array(
                        'conditionType' => $conditionType,
                    ),
                    'properties' => array(
                        'id',
                        'addedVia',
                        'isDraft',
                        'attachedTo',
                        'isFulfilled',
                        'operatingCentre'
                    ),
                    'children' => array(
                        'operatingCentre' => array(
                            'properties' => array(
                                'address',
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'addressLine4',
                                        'town',
                                        'postcode'
                                    ),
                                    'children' => array(
                                        'countryCode' => array(
                                            'properties' => array(
                                                'id'
                                            )
                                        )
                                    )
                                )
                            )
                        ),

                        'attachedTo' => array(
                            'properties' => 'ALL'
                        )
                    )
                )
            )
        );
    }
}
