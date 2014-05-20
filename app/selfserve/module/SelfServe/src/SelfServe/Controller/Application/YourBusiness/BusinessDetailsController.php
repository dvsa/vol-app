<?php

/**
 * BusinessDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * BusinessDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
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
        // @TODO we shouldn't really need to do this; it's only
        // because the $service is Application so we can fetch tradingNames
        return parent::save($data, 'Organisation');
    }

    protected function alterFormBeforeValidation($form)
    {
        $form = parent::alterFormBeforeValidation($form);

        // @TODO alter based on submit button potentially

        return $form;
    }

    protected function alterForm($form)
    {
        $organisation = $this->getOrganisationData(['organisationType']);

        $fieldset = $form->get('data');


        // always set the edit link
        $fieldset->get('edit_business_type')->setValue(
            $this->getUrlFromRoute(
                'Application/YourBusiness/BusinessType',
                ['applicationId' => $this->getIdentifier()]
            )
        );

        switch ($organisation['organisationType']) {
            case 'org_type.lc':
            case 'org_type.llp':
                // no-op; the full form is fine
                break;
            case 'org_type.st':
                $fieldset->remove('name')
                    ->remove('companyNumber');
                break;
            case 'org_type.p':
                $fieldset->remove('companyNumber');
                break;
            case 'org_type.o':
                $fieldset->remove('companyNumber')
                    ->remove('tradingNames');
                break;
        }
        return $form;
    }

    protected function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        $data = parent::processDataMapForSave($oldData, $map, $section);

        // the disabled input will always be null, so ignore it...
        unset($data['organisationType']);

        $data['registeredCompanyNumber'] = $data['companyNumber']['company_number'];
        if (isset($data['tradingNames'])) {
            $licence = $this->getLicenceData(['id']);
            $tradingNames = [];
            foreach ($data['tradingNames']['trading_name'] as $tradingName) {
                $tradingNames[] = [
                    'tradingName' => $tradingName['text'],
                    'licence' => $licence['id'],
                ];
            }
            $data['tradingNames'] = $tradingNames;

            //$this->makeRestCall('TradingNames', 'POST', $tradingNames);
        }

        return $data;
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
        return $this->processLookupCompany(
            parent::getForm($type)
        );
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
}
