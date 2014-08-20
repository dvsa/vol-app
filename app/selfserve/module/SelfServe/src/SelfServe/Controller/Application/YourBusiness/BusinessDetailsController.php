<?php

/**
 * BusinessDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * BusinessDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsController extends YourBusinessController
{
    /**
     * Section service
     *
     * @var string
     */
    protected $service = 'Application';

    /**
     * Section data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'licence' => array(
                'children' => array(
                    'organisation' => array(
                        'children' => array(
                            'type' => array(
                                'properties' => array(
                                    'id'
                                )
                            ),
                            'tradingNames' => array(
                                'properties' => array()
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'CompanySubsidiary';

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'name',
            'companyNo'
        )
    );

    /**
     * Form tables name
     *
     * @var string
     */
    protected $formTables = array(
        'table' => 'application_your-business_business_details-subsidiaries'
    );

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Save data
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
        if (isset($data['companyNumber'])) {
            // unfortunately the company number field is a complex one so can't
            // be mapped directly
            $data['companyOrLlpNo'] = $data['companyNumber']['company_number'];
        }

        if (isset($data['tradingNames'])) {

            $licence = $this->getLicenceData();
            $tradingNames = [];

            foreach ($data['tradingNames']['trading_name'] as $tradingName) {

                if (trim($tradingName['text']) !== '') {
                    $tradingNames[] = [
                        'name' => $tradingName['text']
                    ];
                }
            }

            if (!empty($tradingNames)) {
                $data['tradingNames'] = $tradingNames;

                $tradingNameData = array(
                    'organisation' => $data['id'],
                    'licence' => $licence['id'],
                    'tradingNames' => $tradingNames
                );

                $this->makeRestCall('TradingNames', 'POST', $tradingNameData);
            }
        }

        unset($data['type']);
        unset($data['edit_business_type']);
        unset($data['companyNumber']);
        unset($data['tradingNames']);

        // we shouldn't really need to do this; it's only
        // because our $service property is set to Application
        // so we can fetch tradingNames as a child value
        return parent::save($data, 'Organisation');
    }

    /**
     * Conditionally alter the form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $organisationBundle = array(
            'children' => array(
                'type' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $organisation = $this->getOrganisationData($organisationBundle);

        $fieldset = $form->get('data');

        switch ($organisation['type']['id']) {
            case self::ORG_TYPE_REGISTERED_COMPANY:
            case self::ORG_TYPE_LLP:
                // no-op; the full form is fine
                break;
            case self::ORG_TYPE_SOLE_TRADER:
                $fieldset->remove('name')->remove('companyNumber');
                $form->remove('table');
                break;
            case self::ORG_TYPE_PARTNERSHIP:
                $fieldset->remove('companyNumber');
                $fieldset->get('name')->setLabel($fieldset->get('name')->getLabel() . '.partnership');
                $form->remove('table');
                break;
            case self::ORG_TYPE_OTHER:
                $fieldset->remove('companyNumber')->remove('tradingNames');
                $fieldset->get('name')->setLabel($fieldset->get('name')->getLabel() . '.other');
                $form->remove('table');
                break;
        }

        return $form;
    }

    /**
     * Process load data for form
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        print '<pre>';
        print_r($data);
        exit;
        
        $licence = $data['licence'];
        $organisation = $licence['organisation'];

        $tradingNames = [];

        foreach ($licence['organisation']['tradingNames'] as $tradingName) {
            $tradingNames[] = ['text' => $tradingName['name']];
        }

        $tradingNames[] = ['text' => ''];

        $map = [
            'tradingNames' => [
                'trading_name' => $tradingNames,
            ],
            'companyNumber' => [
                'company_number' => $organisation['companyOrLlpNo']
            ]
        ];

        $data = [
            'data' => array_merge($organisation, $map)
        ];

        if (isset($data['data']['type']['id'])) {
            $data['data']['type'] = $data['data']['type']['id'];
        }

        return $data;
    }

    /**
     * Override getForm
     *
     * @param string $type
     * @return Form
     */
    protected function getForm($type)
    {
        return $this->processLookupCompany(parent::getForm($type));
    }

    /**
     * Generate form with data
     *
     * @todo Should this really be public?
     *
     * @param string $name
     * @param callable $callback
     * @param array $data
     * @param array $tables
     * @return Form
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $request = $this->getRequest();

        $post = (array)$request->getPost();

        if (isset($post['data']['tradingNames']['submit_add_trading_name'])) {

            $this->setPersist(false);
        }

        $form = parent::generateFormWithData($name, $callback, $data, $tables);

        $form = $this->processAddTradingName($form);

        return $form;
    }

    /**
     * Process add trading name
     *
     * @param Form $form
     * @return Form
     */
    protected function processAddTradingName($form)
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $form;
        }

        $post = (array)$request->getPost()['data'];

        if (isset($post['tradingNames']['submit_add_trading_name'])) {

            $form->setValidationGroup(array('data' => ['tradingNames']));

            $form->setData($request->getPost());

            if ($form->isValid()) {

                $tradingNames = $form->getData()['data']['tradingNames']['trading_name'];

                //remove existing entries from collection and check for empty entries
                foreach ($tradingNames as $key => $val) {
                    $form->get('data')->get('tradingNames')->get('trading_name')->remove($key);

                    if (strlen(trim($val['text'])) == 0) {
                        unset($tradingNames[$key]);
                    }
                }

                $tradingNames[] = array('text' => '');

                //reset keys
                $tradingNames = array_values($tradingNames);

                $data = array(
                    'data' => array(
                        'tradingNames' => array('trading_name' => $tradingNames)
                    )
                );

                $form->setData($data);
            }
        }

        return $form;
    }

    /**
     * Process lookup company
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function processLookupCompany(\Zend\Form\Form $form)
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $form;
        }

        $post = (array)$request->getPost()['data'];

        if (isset($post['companyNumber']['submit_lookup_company'])) {

            $this->setPersist(false);

            if (strlen(trim($post['companyNumber']['company_number'])) != 8) {

                $form->get('data')->get('companyNumber')->setMessages(
                    array(
                        'company_number' => array(
                            'The input must be 8 characters long'
                        )
                    )
                );

            } else {

                $result = $this->makeRestCall(
                    'CompaniesHouse',
                    'GET',
                    [
                        'type' => 'numberSearch',
                        'value' => $post['companyNumber']['company_number']
                    ]
                );

                if ($result['Count'] == 1) {

                    $companyName = $result['Results'][0]['CompanyName'];
                    $post['name'] = $companyName;
                    $this->setFieldValue('data', $post);

                } else {

                    $form->get('data')->get('companyNumber')->setMessages(
                        array(
                            'company_number' => array(
                                'Sorry, we couldn\'t find any matching companies, please try again or enter your '
                                . 'details manually below'
                            )
                        )
                    );
                }
            }
        }

        return $form;
    }

    /**
     * Add subsidiary company
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit subsidiary company
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete subsidiary company
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->delete();
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $organisation = $this->getOrganisationData(['type', 'id']);
        $data['organisation'] = $organisation['id'];
        parent::actionSave($data, 'CompanySubsidiary');
    }

    /**
     * Format the data for the form
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        return array('data' => $data);
    }

    /**
     * Get the form table data
     *
     * @return array
     */
    protected function getFormTableData()
    {
        $organisation = $this->getOrganisationData(array('id'));

        $data = $this->makeRestCall(
            $this->getActionService(),
            'GET',
            array('organisation' => $organisation['id']),
            $this->getActionDataBundle()
        );

        return $data;
    }
}
