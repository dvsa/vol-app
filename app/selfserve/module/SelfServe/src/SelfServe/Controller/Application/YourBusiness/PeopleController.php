<?php

/**
 * People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * People Controller
 *
 * @author Alex Peshkov <alex.peshkov@clocal.co.uk>
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
        $applicationId = $this->params()->fromRoute('applicationId');

        $bundle = array(
            'properties' => array(
                'id',
                'title',
                'forename',
                'familyName',
                'birthDate',
                'otherName',
                'position'
            ),
        );

        $data = $this->makeRestCall(
            'Person',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        return array_key_exists('Results', $data) ? $data['Results'] : array();
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

        $orgType = $this->getOrganisationData($bundle);


        $translate = $this->getServiceLocator()->get('viewhelpermanager')->get('translate');
        $guidance = $form->get('guidance')->get('guidance');

        switch ($orgType['type']['id']) {
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

        if ($orgType['type']['id'] != self::ORG_TYPE_OTHER) {
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
        $data = parent::processActionLoad($data);
        $returnData = ($this->getActionName() != 'add') ? array('data' => $data) : $data;

        return $returnData;
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
        $applicationId = $this->getIdentifier();
        $data['application'] = $applicationId;
        parent::actionSave($data, 'Person');
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

        // company is LLP or Limited
        if (in_array($org['type']['id'], array(self::ORG_TYPE_LLP, self::ORG_TYPE_REGISTERED_COMPANY))) {
            // no people added
            if (!$this->peopleAdded()) {
                // valid company number added
                $pattern = '/^[A-Z0-9]{8}$/';
                if (preg_match($pattern, $org['companyOrLlpNo'])) {
                    $result = $this->makeRestCall(
                        'CompaniesHouse',
                        'GET',
                        [
                            'type' => 'currentCompanyOfficers',
                            'value' => $org['companyOrLlpNo']
                        ]
                    );
                    if (is_array($result) && array_key_exists('Results', $result) && count($result['Results'])) {
                        foreach ($result['Results'] as $person) {
                            $person['application'] = $this->getIdentifier();
                            $this->makeRestCall('Person', 'POST', $person);
                        }
                    }
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
        $applicationId = $this->getIdentifier();
        $bundle = array(
            'properties' => array(
                'id',
            ),
        );

        $data = $this->makeRestCall(
            'Person',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        return (array_key_exists('Count', $data) && $data['Count'] > 0);
    }
}
