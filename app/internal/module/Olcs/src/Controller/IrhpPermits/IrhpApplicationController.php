<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Data\Mapper\Permits\NoOfPermits;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create as QaCreateDTO;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CreateFull as CreateDTO;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Grant;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromCancelled;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromValid;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\ReviveFromUnsuccessful;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\ReviveFromWithdrawn;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Terminate;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateFull as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Create as CandidatePermitCreateCmd;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Delete as CandidatePermitDeleteCmd;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Update as CandidatePermitUpdateCmd;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPath;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\RangesByIrhpApplication as RangesDTO;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById as CandidatePermitItemDTO;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as CandidateListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableCountries;
use Dvsa\Olcs\Transfer\Query\IrhpPermitType\ById as PermitTypeQry;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\OpenByType;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceDto;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\BilateralApplicationValidationModifier as BilateralApplicationValidationModifierMapper;
use Olcs\Data\Mapper\IrhpApplication as IrhpApplicationMapper;
use Olcs\Data\Mapper\IrhpCandidatePermit as IrhpCandidatePermitMapper;
use Olcs\Form\Model\Form\IrhpApplication;
use Olcs\Form\Model\Form\IrhpApplicationWithdraw as WithdrawForm;
use Olcs\Form\Model\Form\IrhpCandidatePermit as IrhpCandidatePermitForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use RuntimeException;

class IrhpApplicationController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
    protected $routeIdentifier = 'irhp-application';

    protected $navigationId = 'licence_irhp_permits-application-details';

    // Maps the route parameter irhpPermitId to the "id" parameter in the the ById (ItemDTO) query.
    protected $itemParams = ['id' => 'irhpAppId'];

    // Maps the licence route parameter into the ListDTO as licence => value
    protected $itemDto = ItemDto::class;
    protected $formClass = IrhpApplication::class;
    protected $addFormClass = IrhpApplication::class;
    protected $mapperClass = IrhpApplicationMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $addContentTitle = 'Add Irhp Application';

    // Stores the application steps array retrieved from Q&A
    protected $applicationSteps;

    public const PERMIT_TYPE_LABELS = [
        RefData::IRHP_BILATERAL_PERMIT_TYPE_ID => 'Bilateral',
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => 'Multilateral',
    ];

    public const COR_CERTIFICATE_NUMBER_TYPES = [
        RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
        RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID
    ];

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/irhp-application/application',
            'action' => 'edit',
            'resultIdMap' => [
                'irhpAppId' => 'irhpApplication',
            ],
        ],
        'edit' => [
            'route' => 'licence/irhp-application/application',
            'action' => 'edit',
        ],
        'cancel' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'terminate' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'submit' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'grant' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'withdraw' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'resettonotyetsubmittedfromcancelled' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'resettonotyetsubmittedfromvalid' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'revivefromwithdrawn' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'revivefromunsuccessful' => [
            'route' => 'licence/irhp-application',
            'action' => 'index',
        ],
        'pregrantedit' => [
            'route' => 'licence/irhp-application/application',
            'action' => 'preGrant',
        ],
        'pregrantadd' => [
            'route' => 'licence/irhp-application/application',
            'action' => 'preGrant',
        ],
        'pregrantdelete' => [
            'route' => 'licence/irhp-application/application',
            'action' => 'preGrant',
        ],
    ];

    // Scripts to include when rendering actions.
    protected $inlineScripts = [
        'preGrantAction' => ['table-actions'],
        'preGrantEditAction' => ['forms/irhp-candidate-permit'],
        'preGrantAddAction' => ['forms/irhp-candidate-permit'],
        'selectTypeAction' => ['forms/select-type-modal'],
        'addAction' => ['forms/irhp-bilateral-application'],
        'editAction' => ['forms/irhp-application', 'forms/irhp-bilateral-application'],
    ];
    protected FieldsetPopulator $QaFieldsetPopulator;
    protected BilateralApplicationValidationModifierMapper $bilateralApplicationValidationModifierMapper;
    protected NoOfPermits $noOfPermitsMapper;
    protected IrhpApplicationMapper $irhpApplicationMapper;
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        FieldsetPopulator $QaFieldsetPopulator,
        BilateralApplicationValidationModifierMapper $bilateralApplicationValidationModifierMapper,
        NoOfPermits $noOfPermitsMapper,
        IrhpApplicationMapper $irhpApplicationMapper
    ) {
        $this->QaFieldsetPopulator = $QaFieldsetPopulator;
        $this->bilateralApplicationValidationModifierMapper = $bilateralApplicationValidationModifierMapper;
        $this->noOfPermitsMapper = $noOfPermitsMapper;
        $this->irhpApplicationMapper = $irhpApplicationMapper;

        parent::__construct($translationHelper, $formHelperService, $flashMessenger, $navigation);
    }

    /**
     * Get left view
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();

        $view->setTemplate('sections/irhp-application/partials/left');

        return $view;
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        return $this->redirect()
            ->toRoute(
                'licence/irhp-permits/application',
                [
                    'licence' => $this->params()->fromRoute('licence')
                ]
            );
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function detailsAction()
    {
        return $this->redirect()
            ->toRoute(
                'licence/irhp-application/application',
                [
                    'action' => 'edit',
                ],
                [],
                true
            );
    }

    /**
     * Renders modal form, and handles redirect to correct application form for permit type.
     */
    public function selectTypeAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $permitTypeId = $this->params()->fromPost()['permitType'];

            $routeParams = [
                'licence' => $this->params()->fromRoute('licence'),
                'permitTypeId' => $permitTypeId
            ];

            switch ($permitTypeId) {
                case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                case RefData::ECMT_PERMIT_TYPE_ID:
                    return $this->redirect()
                    ->toRouteAjax(
                        'licence/irhp-application/add',
                        $routeParams,
                        [
                                'query' => [
                                    'year' => $this->params()->fromPost()['year'],
                                    'irhpPermitStock' => $this->params()->fromPost()['stock']
                                ],
                            ]
                    );
                case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                    return $this->redirect()
                    ->toRouteAjax(
                        'licence/irhp-application/add',
                        $routeParams,
                        [
                                'query' => [
                                    'countries' => implode(',', $this->params()->fromPost()['countries'])
                                ],
                            ]
                    );
                case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
                case RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID:
                case RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID:
                    return $this->redirect()->toRouteAjax('licence/irhp-application/add', $routeParams);
            }
        }

        $form = $this->getForm('SelectPermitType');
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', 'Select Permit Type');
        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
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
        $parameters['isPreGrant'] = true;

        return $parameters;
    }

    /**
     * @return mixed|Response
     *
     * Small override to handle the cancel button on the Add form as this form is not shown in a JS modal popup
     */
    public function addAction()
    {
        $typeResponse = $this->handleQuery(PermitTypeQry::create(['id' => $this->params()->fromRoute('permitTypeId')]));
        $irhpPermit = $typeResponse->getResult();

        if ($irhpPermit['isApplicationPathEnabled']) {
            $this->questionAnswerAddApplicationRedirect();
        }

        $this->setFormTitle($this->params()->fromRoute('permitTypeId', null));
        $request = $this->getRequest();
        if ($request->isPost() && array_key_exists('back', (array)$this->params()->fromPost()['form-actions'])) {
            return $this->permitDashRedirect();
        }

        return parent::addAction();
    }

    /**
     * Handles creation of IrhpApplication rows to support QA Application form rendering.
     *
     * @return Response
     */
    protected function questionAnswerAddApplicationRedirect()
    {
        $response = $this->handleCommand(
            QaCreateDTO::create(
                [
                    'licence' => $this->params()->fromRoute('licence'),
                    'irhpPermitType' => $this->params()->fromRoute('permitTypeId'),
                    'irhpPermitStock' => $this->params()->fromQuery('irhpPermitStock'),
                    'fromInternal' => 1,
                ]
            )
        );
        $result = $response->getResult();

        return $this->redirect()
            ->toRoute(
                'licence/irhp-application/application',
                [
                    'licence' => $this->params()->fromRoute('licence'),
                    'action' => 'edit',
                    'irhpAppId' => $result['id']['irhpApplication']
                ]
            );
    }

    /**
     * @return mixed|Response
     *
     * Small override to handle the cancel button on the Edit form
     */
    public function editAction()
    {
        $request = $this->getRequest();
        if ($request->isPost() && array_key_exists('back', (array)$this->params()->fromPost()['form-actions'])) {
            return $this->permitDashRedirect();
        }

        return parent::editAction();
    }

    /**
     * Sets content tile to identify type of application being submitted
     *
     * @param $permitTypeId
     */
    protected function setFormTitle($permitTypeId)
    {
        $type = '';
        if (array_key_exists($permitTypeId, self::PERMIT_TYPE_LABELS)) {
            $type = self::PERMIT_TYPE_LABELS[$permitTypeId];
        }
        $this->addContentTitle = "Add $type Permit Application";
    }

    /**
     * Dash redirect helper
     *
     * @return Response
     */
    protected function permitDashRedirect()
    {
        return $this->redirect()
            ->toRoute(
                'licence/irhp-application',
                ['licence' => $this->params()->fromRoute('licence')]
            );
    }

    /**
     * Handles click of the Submit button on right-sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function submitAction()
    {
        $response = $this->handleQuery(ItemDto::create(['id' => $this->params()->fromRoute('irhpAppId')]));
        $irhpPermit = $response->getResult();

        $feeIds = $this->getOutstandingFeeIds(
            $irhpPermit['fees'],
            [
                RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                RefData::IRHP_GV_ISSUE_FEE_TYPE,
                RefData::IRFO_GV_FEE_TYPE
            ]
        );

        // The application canBeSubmitted, check for an outstanding fee and redirect ICW User to pay screen
        if (!empty($feeIds)) {
            return $this->redirect()
                ->toRoute(
                    'licence/irhp-application-fees/fee_action',
                    [
                        'action' => 'pay-fees',
                        'fee' => implode(',', $feeIds),
                        'licence' => $this->params()->fromRoute('licence'),
                        'irhpAppId' => $this->params()->fromRoute('irhpAppId')
                    ],
                    [],
                    false
                );
        } else {
            // There was no outstanding fee for this application (already been paid) but it is submitable to call handler
            return $this->confirmCommand(
                new ConfirmItem($this->itemParams),
                SubmitApplication::class,
                'Are you sure?',
                'Submit Application. Are you sure?',
                'IRHP Application Submitted'
            );
        }
    }

    /**
     * check for any outstanding fees of the specified types, return the IDs to pass to Fees controller to pay.
     *
     * @param array $fees     Array of fees associated with the application
     * @param array $feeTypes Array of fee types of which we need to know if any are outstanding
     *
     * @return array
     */
    protected function getOutstandingFeeIds(array $fees, array $feeTypes)
    {
        $feeIds = [];
        foreach ($fees as $fee) {
            if (
                $fee['feeStatus']['id'] === RefData::FEE_STATUS_OUTSTANDING
                && in_array($fee['feeType']['feeType']['id'], $feeTypes)
            ) {
                $feeIds[] = $fee['id'];
            }
        }
        return $feeIds;
    }

    /**
     * Handles click of the Cancel button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function cancelAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            CancelApplication::class,
            'Are you sure?',
            'Cancel Application. Are you sure?',
            'IRHP Application Cancelled'
        );
    }

    /**
     * Handles click of the Terminate button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function terminateAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            Terminate::class,
            'Are you sure?',
            'You are about to terminate the selected certificate. Are you sure?',
            'The selected certificate has been terminated',
            'Confirm'
        );
    }

    /**
     * withdraw action
     *
     * @return ViewModel
     */
    public function withdrawAction()
    {
        return $this->add(
            WithdrawForm::class,
            new AddFormDefaultData(['id' => $this->params()->fromRoute('irhpAppId')]),
            Withdraw::class,
            \Olcs\Data\Mapper\IrhpWithdraw::class,
            'pages/crud-form',
            'Withdraw Application',
            'Withdraw Application'
        );
    }

    /**
     * Handles click of the Revive Application button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function reviveFromWithdrawnAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            ReviveFromWithdrawn::class,
            'Are you sure?',
            'Revive Application from withdrawn state. Are you sure?',
            'IRHP Application revived from withdrawn state'
        );
    }

    /**
     * Handles click of the Revive Application button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function reviveFromUnsuccessfulAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            ReviveFromUnsuccessful::class,
            'Are you sure?',
            'Revive Application from unsuccessful state. Are you sure?',
            'IRHP Application revived from unsuccessful state'
        );
    }

    /**
     * Handles click of the Grant button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function grantAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            Grant::class,
            'Are you sure?',
            'Grant Application. Are you sure?',
            'IRHP Application Granted'
        );
    }

    /**
     * Handles click of the Reset to Not Yet Submitted From Cancelled button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function resetToNotYetSubmittedFromCancelledAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            ResetToNotYetSubmittedFromCancelled::class,
            'Are you sure?',
            'Are you sure you want to reset to Not Yet Submitted?',
            'IRHP Application status updated'
        );
    }

    /**
     * Handles click of the Reset to Not Yet Submitted From Valid button on right sidebar
     *
     * @return \Laminas\Http\Response
     */
    public function resetToNotYetSubmittedFromValidAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams),
            ResetToNotYetSubmittedFromValid::class,
            'Are you sure?',
            'Are you sure you want to reset to Not Yet Submitted?',
            'IRHP Application status updated'
        );
    }

    /**
     * Setup required values for Add form
     *
     * @param  $form
     * @param  $formData
     * @return mixed
     * @throws NotFoundException
     */
    protected function alterFormForAdd($form, $formData)
    {
        $licence = $this->getLicence();
        $permitTypeId = $this->params()->fromRoute('permitTypeId', null);
        $formData['topFields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['topFields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $formData['topFields']['dateReceived'] = date("Y-m-d");
        $formData['topFields']['irhpPermitType'] = $permitTypeId;
        $formData['topFields']['licence'] = $this->params()->fromRoute('licence', null);

        if ($permitTypeId == RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID) {
            $form->get('topFields')->remove('addOrRemoveCountriesButton');

            $maxStockPermits = $this->handleQuery(
                MaxStockPermits::create(['licence' => $this->params()->fromRoute('licence', null)])
            );
            if (!$maxStockPermits->isOk()) {
                throw new NotFoundException('Could not retrieve max permits data');
            }
            $formData['maxStockPermits']['result'] = $maxStockPermits->getResult()['results'];

            $windows = $this->getMultilateralWindows()['results'];

            // Prepare data structure with open multilateral windows for NoOfPermits form builder
            $formData['application'] = IrhpApplicationMapper::mapApplicationData(
                $windows,
                $permitTypeId
            );

            // Build the dynamic NoOfPermits per country per year form from Common
            $formData['application']['licence']['totAuthVehicles'] = $licence['totAuthVehicles'];

            $this->noOfPermitsMapper->mapForFormOptions(
                $formData,
                $form,
                'application',
                'maxStockPermits',
                'feePerPermit'
            );
        } elseif ($permitTypeId == RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $formData = array_merge(
                $formData,
                $this->params()->fromPost()
            );

            $response = $this->handleQuery(
                BilateralMetadata::create([])
            );

            if ($this->request->isPost()) {
                $selectedCountryIds = explode(',', $formData['fields']['selectedCountriesCsv']);
            } else {
                // selected countries need to come from querystring
                $selectedCountryIds = explode(',', $this->params()->fromQuery('countries'));
            }
            $formData['selectedCountryIds'] = $selectedCountryIds;

            $formData['bilateralMetadata'] = $response->getResult();
            $this->bilateralApplicationValidationModifierMapper
                ->mapForFormOptions($formData, $form);
        }

        $form->setData($formData);

        $form->get('topFields')->remove('stockHtml');
        $form->get('bottomFields')->remove('checked');

        if (!in_array($formData['topFields']['irhpPermitType'], self::COR_CERTIFICATE_NUMBER_TYPES)) {
            $form->get('bottomFields')->remove('corCertificateNumber');
        }

        return $form;
    }

    /**
     * Setup required values for Edit form
     *
     * @param  $form
     * @param  $formData
     * @return mixed
     *
     * @throws NotFoundException
     */
    protected function alterFormForEdit($form, $formData)
    {
        $licence = $this->getLicence();

        $formData['topFields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['topFields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $formData['topFields']['licence'] = $this->params()->fromRoute('licence', null);

        if ($formData['topFields']['isApplicationPathEnabled']) {
            $form = $this->questionAnswerFormSetup($this->params()->fromRoute('irhpAppId'), $form);
            if ($this->request->isPost()) {
                $formData = $form->updateDataForQa($formData);
            }
        } else {
            $formData = $this->nonQuestionAnswerFormSetup(
                $this->params()->fromRoute('irhpAppId'),
                $form,
                $formData,
                $licence
            );
        }

        if ($formData['topFields']['irhpPermitType'] != RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $form->get('topFields')->remove('addOrRemoveCountriesButton');
        }

        if ($formData['topFields']['irhpPermitType'] == RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $formData['topFields']['stockText'] = $formData['topFields']['stockHtml'] = 'Bilateral permits';
        } elseif (!empty($formData['topFields']['stockText'])) {
            $formData['topFields']['stockHtml'] = $formData['topFields']['stockText'];
        } elseif (!empty($formData['fields']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'])) {
            $irhpPermitStock = $formData['fields']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

            $translator = $this->translationHelperService;
            $stockText = sprintf(
                '%s %s',
                $irhpPermitStock['irhpPermitType']['name']['description'],
                !empty($irhpPermitStock['periodNameKey'])
                    ? $translator->translate($irhpPermitStock['periodNameKey'])
                    : $irhpPermitStock['validityYear']
            );
            $formData['topFields']['stockHtml'] = $stockText;
        }

        $form->setData($formData);

        if (!$formData['topFields']['requiresPreAllocationCheck']) {
            $form->get('bottomFields')->remove('checked');
        }

        if (!in_array($formData['topFields']['irhpPermitType'], self::COR_CERTIFICATE_NUMBER_TYPES)) {
            $form->get('bottomFields')->remove('corCertificateNumber');
        }

        return $form;
    }

    /**
     * @param  int   $irhpApplicationId
     * @param  Form  $form
     * @param  array $formData
     * @param  array $licence
     * @return mixed
     * @throws NotFoundException
     */
    protected function nonQuestionAnswerFormSetup($irhpApplicationId, Form $form, array $formData, array $licence)
    {
        $irhpPermitTypeId = $formData['topFields']['irhpPermitType'];

        if ($irhpPermitTypeId == RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID) {
            $windows = $this->getMultilateralWindows()['results'];

            $formData['application'] = IrhpApplicationMapper::mapApplicationData(
                $windows,
                $formData['topFields']['irhpPermitType'],
                $formData
            );

            $maxStockPermits = $this->handleQuery(
                MaxStockPermits::create(['licence' => $this->params()->fromRoute('licence', null)])
            );

            if (!$maxStockPermits->isOk()) {
                throw new NotFoundException('Could not retrieve max permits data');
            }
            $formData['maxStockPermits']['result'] = $maxStockPermits->getResult()['results'];

            // Build the dynamic NoOfPermits per country per year form from Common
            $formData['application']['licence']['totAuthVehicles'] = $licence['totAuthVehicles'];

            $this->noOfPermitsMapper->mapForFormOptions(
                $formData,
                $form,
                'application',
                'maxStockPermits',
                'feePerPermit'
            );
        } elseif ($irhpPermitTypeId == RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $response = $this->handleQuery(
                BilateralMetadata::create(['irhpApplication' => $irhpApplicationId])
            );
            $formData['bilateralMetadata'] = $response->getResult();

            if ($this->request->isPost()) {
                $postParams = $this->params()->fromPost();
                $selectedCountryIds = explode(',', $postParams['fields']['selectedCountriesCsv']);
            } else {
                foreach ($formData['bilateralMetadata']['countries'] as $country) {
                    if ($country['visible']) {
                        $selectedCountryIds[] = $country['id'];
                    }
                }
            }
            $formData['selectedCountryIds'] = $selectedCountryIds;

            $this->bilateralApplicationValidationModifierMapper
                ->mapForFormOptions($formData, $form);
        } else {
            throw new RuntimeException('Unsupported permit type ' . $irhpPermitTypeId);
        }

        return $formData;
    }

    /**
     * Perform query to obtain application steps for given application ID and populate form.
     *
     * @param  int  $irhpApplicationId
     * @param  Form $form
     * @return mixed
     */
    protected function questionAnswerFormSetup(int $irhpApplicationId, Form $form)
    {
        $response = $this->handleQuery(
            ApplicationPath::create(
                ['id' => $irhpApplicationId]
            )
        );

        $this->applicationSteps = $response->getResult();

        $fieldsetPopulator = $this->QaFieldsetPopulator;
        $fieldsetPopulator->populate($form, $this->applicationSteps, UsageContext::CONTEXT_INTERNAL);

        // remove validation for fieldsets that are not enabled
        $qaInputFilter = $form->getInputFilter()->get('qa');
        foreach ($this->applicationSteps as $applicationStep) {
            if (!$applicationStep['enabled']) {
                $qaInputFilter->remove($applicationStep['fieldsetName']);
            }
        }

        return $form;
    }

    /**
     * @return array|mixed
     * @throws NotFoundException
     */
    protected function getMultilateralWindows()
    {
        $windows = $this->handleQuery(
            OpenByType::create(
                [
                'irhpPermitType' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                ]
            )
        );

        if (!$windows->isOk()) {
            throw new NotFoundException('Could not retrieve open windows');
        }

        return $windows->getResult();
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

    /**
     * Redirect to relevant action, or return index table of candidate permits
     */
    public function preGrantAction()
    {
        $this->navigationId = 'licence_irhp_applications-pregrant';
        $this->setNavigationCurrentLocation();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$this->params()->fromPost();
            switch ($postData['action']) {
                case 'preGrantEdit':
                    return $this->redirect()
                    ->toRoute(
                        'licence/irhp-application/application',
                        [
                                'action' => 'preGrantEdit',
                                'permitId' => $postData['id']
                            ],
                        [],
                        true
                    );
                case 'preGrantDelete':
                    return $this->redirect()
                    ->toRoute(
                        'licence/irhp-application/application',
                        [
                                'action' => 'preGrantDelete',
                                'permitId' => $postData['id']
                            ],
                        [],
                        true
                    );
                case 'preGrantAdd':
                    return $this->redirect()
                    ->toRoute(
                        'licence/irhp-application/application',
                        [ 'action' => 'preGrantAdd'],
                        [],
                        true
                    );
            }
        }

        $this->placeholder()->setPlaceholder('applicationData', IrhpCandidatePermitMapper::mapApplicationData($this->getIrhpApplication()));

        $this->mapperClass = IrhpCandidatePermitMapper::class;

        return $this->index(
            CandidateListDTO::class,
            (new GenericList(['irhpApplication' => 'irhpAppId'], $this->defaultTableSortField, $this->defaultTableOrderField))
                ->setDefaultLimit($this->defaultTableLimit),
            $this->tableViewPlaceholderName,
            'irhp-permits-pre-grant',
            'pages/irhp-permit/pre-grant',
            $this->filterForm
        );
    }

    /**
     * @return array|ViewModel
     */
    public function preGrantEditAction()
    {
        return $this->edit(
            IrhpCandidatePermitForm::class,
            CandidatePermitItemDTO::class,
            new GenericItem(['id' => 'permitId']),
            CandidatePermitUpdateCmd::class,
            IrhpCandidatePermitMapper::class,
            $this->editViewTemplate,
            $this->editSuccessMessage,
            $this->editContentTitle
        );
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function preGrantDeleteAction()
    {
        return $this->confirmCommand(
            new ConfirmItem(['id' => 'permitId']),
            CandidatePermitDeleteCmd::class,
            $this->deleteModalTitle,
            $this->deleteConfirmMessage,
            $this->deleteSuccessMessage
        );
    }

    /**
     * @return mixed|ViewModel
     */
    public function preGrantAddAction()
    {
        return $this->add(
            IrhpCandidatePermitForm::class,
            new AddFormDefaultData(['irhpPermitApplication' => 34252354]),
            CandidatePermitCreateCmd::class,
            IrhpCandidatePermitMapper::class,
            $this->editViewTemplate,
            $this->addSuccessMessage,
            $this->addContentTitle
        );
    }

    /**
     * Utility function to get IrhpApplication relating to ID in the path.
     *
     * @return array|mixed
     * @throws \RuntimeException
     */
    protected function getIrhpApplication()
    {
        $applicationQry = $this->handleQuery(ItemDto::create(['id' => $this->params()->fromRoute('irhpAppId')]));
        if (!$applicationQry->isOk()) {
            throw new \RuntimeException('Error getting application data');
        }
        return $applicationQry->getResult();
    }

    /**
     * AJAX endpoint to return ranges for a given IrhpApplication's stock
     *
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function rangesAction()
    {
        $rangesQry = $this->handleQuery(RangesDTO::create(['irhpApplication' => $this->params()->fromRoute('irhpAppId')]));
        if (!$rangesQry->isOk()) {
            throw new \RuntimeException('Error getting application data');
        }
        return new JsonModel($rangesQry->getResult());
    }

    /**
     * Generate URL for use on Add/Edit pre-grant form
     *
     * @return string
     */
    protected function getRangesUrl()
    {
        return $this->url()->fromRoute(
            'licence/irhp-application/application',
            [
                'action' => 'ranges',
            ],
            [],
            true
        );
    }

    /**
     * Get existing application and populate some required fields on Edit form.
     *
     * @param                                         Form  $form
     * @param                                         array $formData
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function alterFormForPreGrantAdd($form, $formData)
    {
        $irhpApplication = $this->getIrhpApplication();
        $form->get('fields')->get('permitAppId')->setValue($irhpApplication['irhpPermitApplications'][0]['id']);
        $form->get('fields')->get('rangesUrl')->setValue($this->getRangesUrl());

        return $form;
    }

    /**
     * Get existing application and populate some required fields on Add form.
     *
     * @param  Form  $form
     * @param  array $formData
     * @return mixed
     */
    protected function alterFormForPreGrantEdit($form, $formData)
    {
        $form->get('fields')->get('rangesUrl')->setValue($this->getRangesUrl());
        $form->setData($formData);
        return $form;
    }

    /**
     * Retrieves available years list and populates Value options for Add and Edit forms
     *
     * @return JsonModel
     */
    public function availableYearsAction()
    {
        $response = $this->handleQuery(AvailableYears::create(['type' => $this->params()->fromPost('permitType')]));
        $years = [];
        if ($response->isOk()) {
            $years = $response->getResult();
        } else {
            $this->checkResponse($response);
        }

        return new JsonModel($years);
    }

    /**
     * Retrieves available years list and populates Value options for Add and Edit forms
     *
     * @return JsonModel
     */
    public function availableStocksAction()
    {
        $response = $this->handleQuery(
            AvailableStocks::create(
                [
                'irhpPermitType' => $this->params()->fromPost('permitType'),
                'year' => $this->params()->fromPost('year'),
                ]
            )
        );
        $stocks = [];
        if ($response->isOk()) {
            $translator = $this->translationHelperService;
            $stocks = $response->getResult();
            foreach ($stocks['stocks'] as $key => $stock) {
                $stocks['stocks'][$key]['periodName'] = $translator->translate($stock['periodNameKey']);
            }
        } else {
            $this->checkResponse($response);
        }

        return new JsonModel($stocks);
    }

    public function availableCountriesAction()
    {
        $response = $this->handleQuery(AvailableCountries::create([]));

        $jsonCountries = [];
        if ($response->isOk()) {
            $countries = $response->getResult();

            foreach ($countries['countries'] as $country) {
                $jsonCountries[] = [
                    'id' => $country['id'],
                    'name' => $country['countryDesc']
                ];
            }
        } else {
            $this->checkResponse($response);
        }

        return new JsonModel(['countries' => $jsonCountries]);
    }

    public function viewpermitsAction()
    {
        $application = $this->getIrhpApplication();
        return $this->redirect()
            ->toRoute(
                'licence/irhp-application/irhp-permits',
                [
                    'licence' => $this->params()->fromRoute('licence'),
                    'action' => 'index',
                    'permitTypeId' => $application['irhpPermitType']['id'],
                    'irhpAppId' => $this->params()->fromRoute('irhpAppId')
                ]
            );
    }

    /**
     * Map from form
     *
     * @param string $mapperClass
     * @param array  $data
     *
     * @return array
     */
    protected function mapFromForm($mapperClass, array $data)
    {
        if ($mapperClass === IrhpApplicationMapper::class) {
            return $this->irhpApplicationMapper->mapFromForm($data, $this->applicationSteps);
        }

        return parent::mapFromForm($mapperClass, $data);
    }
}
