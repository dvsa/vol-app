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

    protected $service = 'Application';

    protected $dataBundle = [
        'children' => [
            'licence' => [
                'children' => [
                    'organisation',
                    'tradingNames',
                ]
            ],
        ],
    ];

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'name',
            'companyNo',
        ),
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
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'CompanySubsidiary';
    
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
        unset($data['organisationType']);

        if (isset($data['companyNumber'])) {
            // unfortunately the company number field is a complex one so can't
            // be mapped directly
            $data['registeredCompanyNumber'] = $data['companyNumber']['company_number'];
        }

        if (isset($data['tradingNames'])) {

            $licence = $this->getLicenceData();
            $tradingNames = [];

            foreach ($data['tradingNames']['trading_name'] as $tradingName) {

                if (trim($tradingName['text']) !== '') {
                    $tradingNames[] = [
                        'tradingName' => $tradingName['text']
                    ];
                }
            }

            $data['tradingNames'] = $tradingNames;

            $tradingNameData = array(
                'licence' => $licence['id'],
                'tradingNames' => $tradingNames
            );

            $this->makeRestCall('TradingNames', 'POST', $tradingNameData);
        }

        // @TODO we shouldn't really need to do this; it's only
        // because our $service property is set to Application
        // so we can fetch tradingNames as a child value
        return parent::save($data, 'Organisation');
    }

    protected function alterForm($form)
    {
        $organisation = $this->getOrganisationData(['organisationType']);

        $fieldset = $form->get('data');

        switch ($organisation['organisationType']) {
            case 'org_type.lc':
            case 'org_type.llp':
                // no-op; the full form is fine
                break;
            case 'org_type.st':
                $fieldset->remove('name')->remove('companyNumber');
                $form->remove('table');
                break;
            case 'org_type.p':
                $fieldset->remove('companyNumber');
                $fieldset->get('name')->setLabel($fieldset->get('name')->getLabel() . '.partnership');
                $form->remove('table');
                break;
            case 'org_type.o':
                $fieldset->remove('companyNumber')->remove('tradingNames');
                $fieldset->get('name')->setLabel($fieldset->get('name')->getLabel() . '.other');
                $form->remove('table');
                break;
        }
        return $form;
    }

    protected function processLoad($data)
    {
        $licence = $data['licence'];
        $organisation = $licence['organisation'];

        $tradingNames = [];
        foreach ($licence['tradingNames'] as $tradingName) {
            $tradingNames[] = ['text' => $tradingName['tradingName']];
        }
        $tradingNames[] = ['text' => ''];

        $map = [
            'tradingNames' => [
                'trading_name' => $tradingNames,
            ],
            'companyNumber' => [
                'company_number' => $organisation['registeredCompanyNumber']
            ]
        ];

        return [
            'data' => array_merge($organisation, $map)
        ];
    }

    protected function getForm($type)
    {
        $form = parent::getForm($type);

        $form = $this->processLookupCompany($form);

        return $form;
    }

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
                $tradingNames = array_merge($tradingNames);

                $data = array('data' => array(
                    'tradingNames' => array('trading_name' => $tradingNames)
                ));

                $form->setData($data);
            }

        }

        return $form;

    }

    protected function processLookupCompany($form)
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $form;
        }

        $post = (array)$request->getPost()['data'];

        if (isset($post['companyNumber']['submit_lookup_company'])) {

            $this->setPersist(false);

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
                    array('company_number' => array(
                        'Sorry, we couldn\'t find any matching companies, '
                        . 'please try again or enter your details manually below'))
                );
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
        $organisation = $this->getOrganisationData(['organisationType', 'id']);
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
