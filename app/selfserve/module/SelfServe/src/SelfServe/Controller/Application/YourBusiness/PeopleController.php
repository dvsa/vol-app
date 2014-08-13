<?php

/**
 * People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends YourBusinessController
{

    /**
     * Form tables name
     *
     * @var string
     */
    protected $formTables = array(
        'table' => 'application_your-business_people_in_form'
    );

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the action service
     *
     * @var string
     */
    protected $actionService = 'Person';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->populatePeople();

        return $this->renderSection();
    }

    /**
     * Get the form table data
     *
     * @return array
     */
    protected function getFormTableData()
    {
        $org = $this->getOrganisationData();

        $bundle = array(
            'properties' => null,
            'children' => array(
                'person' => array(
                    'properties' => array(
                        'id',
                        'title',
                        'forename',
                        'familyName',
                        'birthDate',
                        'otherName',
                        'position'
                    )
                )
            )
        );

        $data = $this->makeRestCall(
            'OrganisationPerson',
            'GET',
            array('organisation' => $org['id']),
            $bundle
        );

        $tableData = array();

        foreach ($data['Results'] as $result) {
            $tableData[] = $result['person'];
        }

        return $tableData;
    }

    /**
     * Add customisation to the table
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $table = $form->get('table')->get('table')->getTable();

        $bundle = array(
            'children' => array(
                'type' => array(
                    'properties' => array('id')
                )
            )
        );

        $org = $this->getOrganisationData($bundle);


        $translate = $this->getServiceLocator()->get('viewhelpermanager')->get('translate');
        $guidance = $form->get('guidance')->get('guidance');

        switch ($org['type']['id']) {
            case self::ORG_TYPE_REGISTERED_COMPANY:
                $table->setVariable(
                    'title',
                    $translate('selfserve-app-subSection-your-business-people-tableHeaderDirectors')
                );
                $guidance->setValue($translate('selfserve-app-subSection-your-business-people-guidanceLC'));
                break;
            case self::ORG_TYPE_LLP:
                $table->setVariable(
                    'title',
                    $translate('selfserve-app-subSection-your-business-people-tableHeaderPartners')
                );
                $guidance->setValue($translate('selfserve-app-subSection-your-business-people-guidanceLLP'));
                break;
            case self::ORG_TYPE_PARTNERSHIP:
                $table->setVariable(
                    'title',
                    $translate('selfserve-app-subSection-your-business-people-tableHeaderPartners')
                );
                $guidance->setValue($translate('selfserve-app-subSection-your-business-people-guidanceP'));
                break;
            case self::ORG_TYPE_OTHER:
                $table->setVariable(
                    'title',
                    $translate('selfserve-app-subSection-your-business-people-tableHeaderPeople')
                );
                $guidance->setValue($translate('selfserve-app-subSection-your-business-people-guidanceO'));
                break;
            default:
                break;
        }

        if ($org['type']['id'] != self::ORG_TYPE_OTHER) {
            $table->removeColumn('position');
        }

        return $form;
    }

    /**
     * Customize form
     *
     * @param Form $form
     */
    protected function alterActionForm($form)
    {
        $bundle = array(
            'children' => array(
                'type' => array(
                    'properties' => 'id'
                )
            )
        );

        $orgType = $this->getOrganisationData($bundle);

        if ($orgType['type']['id'] != self::ORG_TYPE_OTHER) {
            $form->get('data')->remove('position');
        }
        return $form;
    }

    /**
     * Add person
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit person
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete person
     *
     * @return Response
     */
    public function deleteAction()
    {
        $id = $this->getActionId();

        $results = $this->makeRestCall(
            'OrganisationPerson',
            'GET',
            array('person' => $id),
            array('properties' => array('id'))
        );

        if (isset($results['Count']) && $results['Count'] == 1) {
            $this->makeRestCall('OrganisationPerson', 'DELETE', array('id' => $results['Results'][0]['id']));
        }

        return $this->delete();
    }

    /**
     * Process action load data
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        return array('data' => parent::processActionLoad($data));
    }

    /**
     * Save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($validData, $service = null)
    {
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $person = parent::actionSave($data, 'Person');

        // If we are creating a person, we need to link them to the organisation
        if ($this->getActionName() == 'add') {

            $org = $this->getOrganisationData();

            $orgPersonData = array(
                'organisation' => $org['id'],
                'person' => $person['id']
            );

            parent::actionSave($orgPersonData, 'OrganisationPerson');
        }
    }

    /**
     * We should have this method to display empty form
     *
     * @param int $id
     * @param array
     */
    protected function load($id)
    {
        return array();
    }

    /**
     * Pre-populate people for company
     *
     */
    protected function populatePeople()
    {
        $bundle = array(
            'properties' => array('companyOrLlpNo'),
            'children' => array(
                'type' => array(
                    'properties' => array('id')
                )
            )
        );

        $org = $this->getOrganisationData($bundle);

        $orgTypesOnCompaniesHouse = array(
            self::ORG_TYPE_LLP,
            self::ORG_TYPE_REGISTERED_COMPANY
        );

        // If we are not a limited company or LLP just bail
        // OR if we have already added people
        // OR if we don't have a company number
        if (!in_array($org['type']['id'], $orgTypesOnCompaniesHouse)
            || $this->peopleAdded()
            || !preg_match('/^[A-Z0-9]{8}$/', $org['companyOrLlpNo'])) {
            return;
        }

        $searchData = array(
            'type' => 'currentCompanyOfficers',
            'value' => $org['companyOrLlpNo']
        );

        $result = $this->makeRestCall('CompaniesHouse', 'GET', $searchData);

        if (is_array($result) && array_key_exists('Results', $result) && count($result['Results'])) {

            // @todo We need a better way to handle this, far too many rest calls could happen
            foreach ($result['Results'] as $person) {

                // Create a person
                $person = $this->makeRestCall('Person', 'POST', $person);

                // If we have a person id
                if (isset($person['id'])) {

                    $organisationPersonData = array(
                        'organisation' => $org['id'],
                        'person' => $person['id']
                    );

                    $this->makeRestCall('OrganisationPerson', 'POST', $organisationPersonData);
                }
            }
        }
    }

    /**
     * Determine if people already added for current application
     *
     * @return bool
     */
    protected function peopleAdded()
    {
        $org = $this->getOrganisationData();

        $bundle = array('properties' => array('id'));

        $data = $this->makeRestCall('OrganisationPerson', 'GET', array('organisation' => $org['id']), $bundle);

        return (array_key_exists('Count', $data) && $data['Count'] > 0);
    }
}
