<?php

/**
 * IRHP Permit Application Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\Permits\CancelEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\WithdrawEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\GetAllByLicence as ListDTO;
use Dvsa\Olcs\Transfer\Query\Permits\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceDto;
use Dvsa\Olcs\Transfer\Query\Permits\Sectors as SectorsDto;
use Dvsa\Olcs\Transfer\Command\Permits\CreateFullPermitApplication as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitApplication as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Permits\DeclineEcmtPermits as DeclineDTO;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpPermitApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrhpPermitApplication as IrhpPermitApplicationMapper;
use Olcs\Form\Model\Form\PermitCreate;
use Zend\View\Model\ViewModel;

class IrhpPermitApplicationController extends AbstractInternalController implements
    IrhpPermitApplicationControllerInterface,
    LeftViewProvider,
    ToggleAwareInterface
{
    const FEE_TYPE_ECMT_APP = 'IRHPGVAPP';
    const FEE_TYPE_ECMT_ISSUE = 'IRHPGVISSUE';

    const ECMT_ANNUAL_PERMIT_TYPE_ID = 1;
    const ECMT_SHORT_TERM_PERMIT_TYPE_ID = 2;
    const ECMT_REMOVAL_PERMIT_TYPE_ID = 3;

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_ECMT
        ],
    ];

    protected $routeIdentifier = 'permits';

    // Maps the route parameter irhpPermitId to the "id" parameter in the the ById (ItemDTO) query.
    protected $itemParams = ['id' => 'permitid'];

    protected $deleteParams = ['id' => 'permitid'];

    // Setup the default index table and sort columns/order
    protected $tableName = 'permit-applications';
    protected $defaultTableSortField = 'dateReceived, applicationRef';
    protected $defaultTableOrderField = 'DESC, DESC';

    // Maps the licence route parameter into the ListDTO as licence => value
    protected $listVars = ['licence'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $formClass = PermitCreate::class;
    protected $addFormClass = PermitCreate::class;
    protected $mapperClass = IrhpPermitApplicationMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove IRHP Permit Application?';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this permit application?';
    protected $deleteSuccessMessage = 'The permit stock has been removed';
    protected $addContentTitle = 'Add Irhp Permit Application';
    protected $indexPageTitle = 'IRHP Permits';

    // This tab has two tables unlike most other index pages, so set custom template
    protected $tableViewTemplate = 'pages/irhp-permit/two-tables';
    protected $editViewTemplate = 'pages/irhp-permit/edit';
    protected $filterForm = 'PermitsHome';

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'edit' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'accept' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'decline' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'withdraw' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'cancel' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'submit' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ]

    ];

    // Maps to ID in navgiation-config file to underline correct item in horizontal nav menu

    // Scripts to include when rendering actions.
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'editAction' => ['permits'],
        'addAction' => ['permits']
    ];

    // Override default index action to handle POSTs appropriately and perform the Query for the second table.

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        $navigation->findOneBy('id', 'licence_irhp_permits')->setActive();

        $this->handleIndexPost();
        $this->indexIssuedTable();

        return parent::indexAction();
    }

    /**
     * Extra parameters
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['irhpApplicationStatuses'] = [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
            RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
            RefData::PERMIT_APP_STATUS_AWAITING_FEE,
            RefData::PERMIT_APP_STATUS_FEE_PAID,
            RefData::PERMIT_APP_STATUS_ISSUING,
            RefData::PERMIT_APP_STATUS_CANCELLED,
            RefData::PERMIT_APP_STATUS_WITHDRAWN,
            RefData::PERMIT_APP_STATUS_UNSUCCESSFUL,
        ];

        return $parameters;
    }

    /**
     * Get left view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'irhp_permits',
                'navigationTitle' => 'Application details'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @return mixed|ViewModel
     *
     * Small override to handle the back button on the Add form and set some default form values
     *
     */
    public function addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost() && array_key_exists('back', (array)$this->params()->fromPost()['form-actions'])) {
            return $this->permitDashRedirect();
        }

        return parent::addAction();
    }

    /**
     * Renders modal form, and handles redirect to correct application form for permit type.
     *
     * @return \Zend\Http\Response
     */
    public function selectTypeAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $permitTypeId = $this->params()->fromPost()['permitType'];

            // Temporary redirect to manually built application forms, when other 2 ECMT manual builds
            // are done this switch can be added too, when Generic forms system done will just redirect there passing ID
            switch ($permitTypeId) {
                case self::ECMT_ANNUAL_PERMIT_TYPE_ID:
                    return $this->redirect()
                        ->toRouteAjax(
                            'licence/permits/add',
                            ['licence' => $this->params()->fromRoute('licence')],
                            ['query' => ['permitTypeId' => $permitTypeId]]
                        );
                case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                    return $this->redirect()
                        ->toRouteAjax(
                            'licence/irhp-application/add',
                            [   'licence' => $this->params()->fromRoute('licence'),
                                'permitTypeId' => $permitTypeId
                            ]
                        );
            }
        }

        $form = $this->getForm('SelectPermitType');
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', 'Select Permit Type');
    }

    /**
     * @return mixed|ViewModel
     */
    public function editAction()
    {
        $this->setNavigationId('edit');
        $request = $this->getRequest();
        if ($request->isPost() && array_key_exists('back', (array)$this->params()->fromPost()['form-actions'])) {
            return $this->permitDashRedirect();
        }
        return parent::editAction();
    }


    /**
     * @return \Zend\Http\Response
     *
     * Override to handle the Table from POST when Apply clicked and redirect to the Add form.
     *
     */
    protected function handleIndexPost()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$this->params()->fromPost();
            if ($postData['action'] === 'Apply') {
                return $this->redirect()
                    ->toRoute(
                        'licence/permits/selectType',
                        [
                            'licence' => $this->params()->fromRoute('licence')
                        ]
                    );
            }
        }
    }

    /**
     *
     * Helper method to perform the query and setup table for Issued Permits table on dash.
     *
     */
    protected function indexIssuedTable()
    {
        $response = $this->handleQuery(
            ListDTO::create(
                [
                    'licence' => $this->params()->fromRoute('licence'),
                    'irhpApplicationStatuses' => [
                        RefData::PERMIT_APP_STATUS_VALID,
                    ],
                    'sort' => 'applicationRef',
                    'order' => 'ASC',
                ]
            )
        );

        $data = [];
        if ($response->isOk()) {
            $data = $response->getResult();
        } else {
            $this->checkResponse($response);
        }

        $issuedTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('issued-permits', $data);
        $this->placeholder()->setPlaceholder('issuedTable', $issuedTable);
    }

    /**
     *
     * Dash redirect helper
     *
     */
    protected function permitDashRedirect()
    {
        return $this->redirect()
            ->toRoute(
                'licence/permits',
                ['licence' => $this->params()->fromRoute('licence')]
            );
    }

    /**
     * Setup required values for Edit form
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForEdit($form, $formData)
    {
        $licence = $this->getLicence();
        $formData['fields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['fields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $form = $this->getSectors($form, $formData['fields']['sectors']);
        $form->setData($formData);
        return $form;
    }

    /**
     * Setup required values for Add form
     *
     * @param $form
     * @param $formData
     * @return mixed
     *
     */
    protected function alterFormForAdd($form, $formData)
    {
        $licence = $this->getLicence();
        $formData['fields']['licence'] = $licence['id'];
        $formData['fields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['fields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $formData['fields']['dateReceived'] = date("Y-m-d");
        $form = $this->getSectors($form);
        $form->setData($formData);
        return $form;
    }

    /**
     * Retrieves sectors list and populates Value options for Add and Edit forms
     *
     * @param $form
     * @param null $selectedSector
     * @return mixed
     */
    protected function getSectors($form, $selectedSector = null)
    {
        $response = $this->handleQuery(SectorsDto::create([]));
        $sectors = [];
        if ($response->isOk()) {
            $sectors = $response->getResult();
        } else {
            $this->checkResponse($response);
        }

        $mappedSectors = IrhpPermitApplicationMapper::mapSectors($sectors, $selectedSector);
        $form->get('fields')->get('sectors')->setValueOptions($mappedSectors);

        return $form;
    }

    /**
     * Handles click of the Submit button on right-sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function submitAction()
    {
        $response = $this->handleQuery(ItemDto::create(['id' => $this->params()->fromRoute('permitid')]));
        $irhpPermit = $response->getResult();
        $fee = $this->getOutstandingFee($irhpPermit['fees'], self::FEE_TYPE_ECMT_APP);
        // The application canBeSubmitted, check for an outstanding fee and redirect ICW User to pay screen
        if ($fee) {
            return $this->redirect()
                ->toRoute(
                    'licence/irhp-fees/fee_action',
                    [
                        'action' => 'pay-fees',
                        'fee' => $fee['id'],
                        'licence' => $this->params()->fromRoute('licence'),
                        'permitid' => $this->params()->fromRoute('permitid')
                    ],
                    [],
                    false
                );
        } elseif ($irhpPermit['canBeSubmitted']) {
            // There was no outstanding fee for this application (already been paid) but it is submitable to call handler
            return $this->confirmCommand(
                new ConfirmItem($this->deleteParams),
                EcmtSubmitApplication::class,
                'Are you sure?',
                'Submit Application. Are you sure?',
                'Permit Application Submitted'
            );
        }
    }

    /**
     * Handles click of the Withdraw button on right-sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function withdrawAction()
    {
        $withdrawParams = [
            'id' => $this->params()->fromRoute('permitid'),
            'reason' => RefData::PERMIT_APP_WITHDRAW_REASON_USER,
        ];

        return $this->confirmCommand(
            new AddFormDefaultData($withdrawParams),
            WithdrawEcmtPermitApplication::class,
            'Are you sure?',
            'Withdraw Application. Are you sure?',
            'Permit Application withdrawn'
        );
    }

    /**
     * Handles click of the Cancel button on right sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function cancelAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->deleteParams),
            CancelEcmtPermitApplication::class,
            'Are you sure?',
            'Cancel Permit Application. Are you sure?',
            'Permit Application cancelled'
        );
    }

    /**
     * Handles click of the Decline button on right sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function declineAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->deleteParams),
            DeclineDTO::class,
            'Are you sure?',
            'Decline Permits. Are you sure?',
            'Offer of permits successfully declined.'
        );
    }

    /**
     * Handles click of the Accept button on right sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function acceptAction()
    {
        $response = $this->handleQuery(ItemDto::create(['id' => $this->params()->fromRoute('permitid')]));
        $irhpPermit = $response->getResult();
        $fee = $this->getOutstandingFee($irhpPermit['fees'], self::FEE_TYPE_ECMT_ISSUE);
        if ($fee) {
            return $this->redirect()
                ->toRoute(
                    'licence/irhp-fees/fee_action',
                    [
                        'action' => 'pay-fees',
                        'fee' => $fee['id']
                    ],
                    [],
                    true
                );
        } elseif ($irhpPermit['isAwaitingFee']) {
            // There was no outstanding fees for this application (already been paid) so they have been
            // paid or waived, so allow acceptance to progress.
            return $this->confirmCommand(
                new ConfirmItem($this->deleteParams),
                AcceptEcmtPermits::class,
                'Are you sure?',
                'Accept ECMT Permit Offer. Are you sure?',
                'Permit Application Accepted'
            );
        }
    }


    /**
     * check for and return an outstanding free from the ['fees'] array on the permit application entity
     *
     * @param $fees
     * @param $type
     * @return bool
     */
    protected function getOutstandingFee($fees, $type)
    {
        foreach ($fees as $key => $fee) {
            if ($fee['feeStatus']['id'] === RefData::FEE_STATUS_OUTSTANDING
                && $fee['feeType']['feeType']['id'] === $type) {
                return ($fees[$key]);
            }
        }
        return false;
    }

    /**
     * Check command handler response
     *
     * @param $response
     * @return null
     */
    protected function checkResponse($response)
    {
        if ($response->isOk()) {
            $this->getServiceLocator()
                ->get('Helper\FlashMessenger')
                ->addSuccessMessage('Application Updated Sucessfully');
        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }
    }

    /**
     * @return array|mixed
     * @throws NotFoundException
     */
    protected function getLicence()
    {
        $response = $this->handleQuery(LicenceDto::create(['id' => $this->params()->fromRoute('licence', null)]));
        if (!$response->isOk()) {
            throw new NotFoundException('Could not find Licence');
        }

        return $response->getResult();
    }

    protected function setNavigationId($action)
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        $navigation->findOneBy('id', 'licence_irhp_permits-' . $action)->setActive();
    }
}
