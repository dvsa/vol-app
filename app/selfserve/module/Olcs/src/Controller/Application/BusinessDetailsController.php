<?php

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;
use Common\Controller\Traits\Lva\BusinessDetailsTrait;

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsController extends AbstractApplicationController
{
    use BusinessDetailsTrait;

    /**
     * Business details section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $orgId = $this->getCurrentOrganisationId();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $orgData = $this->getEntityService('Organisation')->getBusinessDetailsData($orgId);
            $data = $this->formatDataForForm($orgData);
        }

        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\BusinessDetails')
            ->setData($data);

        $tableData = $this->getEntityService('CompanySubsidiary')
            ->getAllForOrganisation($orgId);

        $table = $this->getServiceLocator()
            ->get('Table')
            ->buildTable(
                'application_your-business_business_details-subsidiaries',
                $tableData,
                array(), // params?
                false
            );

        $form->get('table')  // fieldset
            ->get('table')   // element
            ->setTable($table);

        if ($request->isPost()) {
            /**
             * we'll re-use this in a few places, so cache the lookup
             * just for the sake of legibility
             */
            $tradingNames = $data['data']['tradingNames'];

            if (isset($data['data']['companyNumber']['submit_lookup_company'])) {
                /**
                 * User has pressed 'Find company' on registered company number
                 */
                if (strlen(trim($data['data']['companyNumber']['company_number'])) != 8) {

                    $form->get('data')->get('companyNumber')->setMessages(
                        array(
                            'company_number' => array(
                                'The input must be 8 characters long'
                            )
                        )
                    );

                } else {
                    $result = $this->getServiceLocator()
                        ->get('CompaniesHouse')
                        ->search('numberSearch', $data['data']['companyNumber']['company_number']);

                    if ($result['Count'] == 1) {

                        $companyName = $result['Results'][0]['CompanyName'];
                        $form->get('data')->get('name')->setValue($companyName);

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
            } elseif (isset($tradingNames['submit_add_trading_name'])) {
                /**
                 * User has pressed 'Add another' on trading names
                 */
                $form->setValidationGroup(array('data' => ['tradingNames']));

                if ($form->isValid()) {

                    // remove existing entries from collection and check for empty entries
                    $names = [];
                    foreach ($tradingNames['trading_name'] as $key => $val) {
                        if (strlen(trim($val['text'])) > 0) {
                            $names[] = $val;
                        }
                    }
                    $names[] = ['text' => ''];

                    $form->get('data')->get('tradingNames')->get('trading_name')->populateValues($names);
                }
            } elseif ($form->isValid()) {
                /**
                 * Normal submission; save the form data
                 */
                if (count($tradingNames['trading_name'])) {
                    $tradingNames = $this->formatTradingNamesDataForSave($orgId, $data);
                    $this->getEntityService('TradingNames')->save($tradingNames);
                }

                $saveData = $this->formatDataForSave($data);
                $saveData['id'] = $orgId;
                $this->getEntityService('Organisation')->save($saveData);

                if (isset($data['table']['action'])) {
                    $action = strtolower($data['table']['action']);

                    if ($action === 'add') {
                        $routeParams = array(
                            'action' => 'add'
                        );
                    } elseif ($action === 'edit') {
                        $routeParams = array(
                            'action' => 'edit',
                            'child_id' => $data['table']['id']
                        );
                    } else {
                        $routeParams = array();
                        $this->getEntityService('CompanySubsidiary')
                            ->delete($data['table']['id']);
                    }

                    return $this->redirect()->toRoute(
                        'lva-application/business_details',
                        $routeParams,
                        array(),
                        true
                    );
                }

                return $this->completeSection('business_details');
            }
        }

        return $this->render('business_details', $form);
    }

    public function addAction()
    {
        return $this->addOrEdit(true);
    }

    public function editAction()
    {
        return $this->addOrEdit(false);
    }

    private function addOrEdit($add = true)
    {
        $mode = $add ? 'add' : 'edit';
        $orgId = $this->getCurrentOrganisationId();
        $request = $this->getRequest();

        $data = array();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $data = $this->formatSubsidiaryDataForForm(
                $this->getEntityService('CompanySubsidiary')->getById($this->params('child_id'))
            );
        }

        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\BusinessDetailsSubsidiaryCompany')
            ->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatSubsidiaryDataForSave($data);
            $data['organisation'] = $orgId;

            $this->getEntityService('CompanySubsidiary')->save($data);

            $routeParams = array(
                'id' => $this->getApplicationId()
            );
            if ($this->isButtonPressed('addAnother')) {
                $routeParams['action'] = 'add';
            }
            return $this->redirect()->toRoute(
                'lva-application/business_details',
                $routeParams
            );
        }

        return $this->render($mode . '_subsidiary_company', $form);
    }

    private function formatTradingNamesDataForSave($organisationId, $data)
    {
        $tradingNames = [];

        foreach ($data['data']['tradingNames']['trading_name'] as $tradingName) {
            if (trim($tradingName['text']) !== '') {
                $tradingNames[] = [
                    'name' => $tradingName['text']
                ];
            }
        }

        $data['tradingNames'] = $tradingNames;

        return array(
            'organisation' => $organisationId,
            'licence' => $this->getLicenceId(),
            'tradingNames' => $tradingNames
        );
    }

    /**
     * Override built-in cancel functionality; need
     * to check if we're on a sub action
     *
     * Might be able to squeeze this into the abstract,
     * will see how consistent other sub actions are first
     */
    protected function handleCancelRedirect($lvaId)
    {
        if ($this->params('action') !== 'index') {
            return $this->redirect()->toRoute(
                'lva-application/business_details',
                array('id' => $lvaId)
            );
        }
        return parent::handleCancelRedirect($lvaId);
    }
}
