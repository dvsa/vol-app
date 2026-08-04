<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper\Lva\CommunityLicence as CommunityLicMapper;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Application\Create as ApplicationCreateCommunityLic;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Application\CreateOfficeCopy as ApplicationCreateOfficeCopy;
use Dvsa\Olcs\Transfer\Command\CommunityLic\EditSuspension as EditSuspensionDto;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Licence\Create as LicenceCreateCommunityLic;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Licence\CreateOfficeCopy as LicenceCreateOfficeCopy;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint as ReprintDto;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Restore as RestoreDto;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Stop as StopDto;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicence;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use RuntimeException;
use LmcRbacMvc\Service\AuthorizationService;
use Common\Service\Cqrs\Response;

/**
 * Shared logic between Community Licences controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractCommunityLicencesController extends AbstractController
{
    use Traits\CrudTableTrait;

    public $totActiveCommunityLicences;

    // See OLCS-16655, pagination is to be 50 per page only
    public const TABLE_RESULTS_PER_PAGE = 50;

    protected $section = 'community_licences';

    protected string $baseRoute = 'lva-%s/community_licences';

    protected $officeCopy;

    private $licenceData;

    /**
     * @var int|null
     */
    protected $totalActiveCommunityLicences;

    protected $defaultFilters = [
        'status' => [
            RefData::COMMUNITY_LICENCE_STATUS_PENDING,
            RefData::COMMUNITY_LICENCE_STATUS_ACTIVE,
            RefData::COMMUNITY_LICENCE_STATUS_WITHDRAWN,
            RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED
        ]
    ];

    protected $filters = [];

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected CommandService $commandService
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Community Licences section
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $crudAction = $this->getCrudAction([$data['table']]);

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction, ['add', 'add office licence']);
            }

            return $this->completeSection('community_licences');
        }

        $filterStatuses = $this->params()->fromQuery('status');
        $hasFiltered = $this->params()->fromQuery('isFiltered');

        if (empty($filterStatuses) && empty($hasFiltered)) {
            $this->filters = $this->defaultFilters;
        } else {
            $this->filters = [
                'status' => empty($filterStatuses) ? 'NULL' : $filterStatuses
            ];
        }

        $filterForm = $this->getFilterForm()->setData($this->filters);

        $form = $this->getForm();
        $this->alterFormForGoodsOrPsv($form);
        $this->alterFormForLva($form);
        $data = $this->formatDataForForm($this->getFormData());
        $form->setData($data);

        $this->scriptFactory->loadFiles(['forms/filter', 'community-licence']);

        $title = 'lva.section.title.community_licences';
        if ($this->getGoodsOrPsv() == RefData::LICENCE_CATEGORY_PSV) {
            $title .= '.psv';
        }

        return $this->render('community_licences', $form, ['filterForm' => $filterForm, 'title' => $title]);
    }

    /**
     * Alter form for goods or psv
     */
    private function alterFormForGoodsOrPsv(Form $form): void
    {
        if ($this->getGoodsOrPsv() == RefData::LICENCE_CATEGORY_PSV) {
            $activeLicencesElement = $form->get('data')->get('totalActiveCommunityLicences');
            $activeLicencesElement->setLabel(
                $activeLicencesElement->getLabel() . '.psv'
            );
        }
    }

    /**
     * Whether the current action is in a goods or psv context
     *
     * @return string
     */
    private function getGoodsOrPsv()
    {
        if (is_null($this->licenceData)) {
            $response = $this->handleQuery(
                Licence::create(['id' => $this->getLicenceId()])
            );
            $this->licenceData = $response->getResult();
        }

        if ($this->lva == self::LVA_APP || $this->lva == self::LVA_VAR) {
            $goodsOrPsvSource = $this->deriveCurrentApplicationData($this->licenceData);
        } else {
            $goodsOrPsvSource = $this->licenceData;
        }

        return $goodsOrPsvSource['goodsOrPsv']['id'];
    }

    /**
     * Return the data associated with the current application from the array returned by the call to the Licence query
     *
     *
     * @return array
     */
    private function deriveCurrentApplicationData(array $licence)
    {
        $applicationId = $this->getApplicationId();

        foreach ($licence['applications'] as $application) {
            if ($application['id'] == $applicationId) {
                return $application;
            }
        }

        throw new RuntimeException('Unable to find application ' . $applicationId . ' in licence data');
    }

    /**
     * Get filter form
     *
     * @return \Laminas\Form\Form
     */
    private function getFilterForm()
    {
        /** @var Form $form */
        $form = $this->formHelper
            ->createForm('Lva\CommunityLicenceFilter', false);

        $lva = ($this->lva !== 'variation') ? $this->lva : 'application';

        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                $this->getBaseRoute(),
                [$lva => $this->getIdentifier()]
            )
        );

        return $form;
    }

    /**
     * Get form
     *
     * @return \Laminas\Form\FormInterface
     */
    private function getForm()
    {
        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm();

        $table = $this->alterTable($this->getTableConfig());
        $this->formHelper->populateFormTable($form->get('table'), $table);
        $this->formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $form;
    }

    /**
     * Get Table
     *
     * @return TableBuilder
     */
    private function getTableConfig()
    {
        $licenceData = $this->getTableData();

        /** @var TableBuilder $table */
        $table = $this->table()->buildTable(
            'community.licence',
            $licenceData,
            $this->params()->fromQuery(),
            false
        );

        if ($licenceData['count'] > 0) {
            $table->addAction(
                'annul',
                ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Annul']
            );
            $table->addAction(
                'stop',
                ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Stop']
            );
            $table->addAction(
                'reprint',
                ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Reprint']
            );
        }

        return $table;
    }

    /**
     * Get Table Data
     *
     * @return array
     *
     * @psalm-suppress all
     */
    private function getTableData()
    {
        /**
         * @VOL GenericList lives in olcs-internal so shouldn't be used here, ticket to fix VOL-5194
         * @phpstan-ignore-next-line
         */
        $paramProvider = new GenericList(['licence']);
        $paramProvider->setParams($this->plugin('params'));

        $listParams = $paramProvider->provideParameters();
        $listParams['statuses'] = implode(',', $this->filters['status']);
        $listParams['limit'] = self::TABLE_RESULTS_PER_PAGE;
        $listParams['licence'] = $this->getLicenceId();

        $response = $this->handleQuery(CommunityLicences::create($listParams));

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $results = [];

        if ($response->isOk()) {
            $results = $response->getResult();
            $this->officeCopy = $results['extra']['officeCopy'];
            $this->totActiveCommunityLicences = $results['extra']['totActiveCommunityLicences'];
        }

        return $results;
    }

    /**
     * Get form data
     *
     * @return array
     */
    private function getFormData()
    {
        return [
            'totActiveCommunityLicences' => $this->totActiveCommunityLicences,
        ];
    }

    /**
     * Format data for form
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatDataForForm($data)
    {
        return [
            'data' => [
                'totalActiveCommunityLicences' => $data['totActiveCommunityLicences'],
            ],
        ];
    }

    /**
     * Hide Add Office Licence action, if necessary
     *
     * @param \Common\Service\Table\TableBuilder $table Table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function alterTable($table)
    {
        $officeCopy = $this->officeCopy;
        if ($officeCopy) {
            $table->removeAction('office-licence-add');
        }

        if (
            !$this->checkTableForLicences(
                $table,
                [
                RefData::COMMUNITY_LICENCE_STATUS_PENDING,
                RefData::COMMUNITY_LICENCE_STATUS_ACTIVE,
                RefData::COMMUNITY_LICENCE_STATUS_WITHDRAWN,
                RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED
                ]
            )
        ) {
            $table->removeAction('void');
        }

        if (
            !$this->checkTableForLicences(
                $table,
                [
                RefData::COMMUNITY_LICENCE_STATUS_WITHDRAWN,
                RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED
                ]
            )
        ) {
            $table->removeAction('restore');
        }

        if (!$this->checkTableForLicences($table, [RefData::COMMUNITY_LICENCE_STATUS_ACTIVE])) {
            $table->removeAction('stop');
            $table->removeAction('reprint');
        }

        return $table;
    }

    /**
     * Check table for active licences
     *
     * @param \Common\Service\Table\TableBuilder $table    Table
     * @param array                              $statuses Statuses
     *
     * @return bool
     */
    protected function checkTableForLicences($table, $statuses)
    {
        $rows = $table->getRows();
        foreach ($rows as $row) {
            if (in_array($row['status']['id'], $statuses)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Office licence add acion
     *
     * @return \Laminas\Http\Response
     */
    public function addOfficeLicenceAction()
    {
        if ($this->lva === 'licence') {
            $create = [
                'licence' => $this->getLicenceId(),
            ];
            $dto = LicenceCreateOfficeCopy::create($create);
        } else {
            $create = [
                'licence' =>  $this->getLicenceId(),
                'identifier' => $this->getIdentifier()
            ];
            $dto = ApplicationCreateOfficeCopy::create($create);
        }

        return $this->processDto($dto, 'internal.community_licence.office_copy_created');
    }

    /**
     * Redirect to index
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'index', $this->getIdentifierIndex() => $this->getIdentifier()],
            ['query' => $this->getRequest()->getQuery()->toArray()]
        );
    }

    /**
     * Add action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $licenceId = $this->getLicenceId();

        $form = $this->formHelper->createForm('Lva\CommunityLicencesAdd');
        $this->formHelper->setFormActionFromRequest($form, $request);

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        if ($request->isPost()) {
            $identifier = $this->getIdentifier();
            $data = (array)$request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($this->lva === 'licence') {
                    $create = [
                        'licence' => $licenceId,
                        'totalLicences' => $data['data']['total'],
                    ];
                    $dto = LicenceCreateCommunityLic::create($create);
                } else {
                    $create = [
                        'licence' => $licenceId,
                        'totalLicences' => $data['data']['total'],
                        'identifier' => $identifier
                    ];
                    $dto = ApplicationCreateCommunityLic::create($create);
                }

                return $this->processDto($dto, 'internal.community_licence.licences_created');
            }
        }

        return $this->render($view);
    }

    /**
     * Annul action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function annulAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $ids = explode(',', $this->params('child_id'));
        if (!$request->isPost()) {
            $form = $this->formHelper->createForm('Lva\CommunityLicencesAnnul');
            $this->formHelper->setFormActionFromRequest($form, $this->getRequest());

            $view = new ViewModel(['form' => $form]);
            $view->setTemplate('partials/form');

            return $this->render($view);
        }

        if (!$this->isButtonPressed('cancel')) {
            $void = [
                'licence' => $this->getLicenceId(),
                'communityLicenceIds' => $ids,
                'checkOfficeCopy' => true
            ];

            if ($this->lva !== 'licence') {
                $void['application'] = $this->getIdentifier();
            }

            return $this->processDto(
                TransferCmd\CommunityLic\Annul::create($void),
                'internal.community_licence.licences_annulled'
            );
        }

        return $this->redirectToIndex();
    }

    /**
     * Restore action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function restoreAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $form = $this->formHelper->createForm('Lva\CommunityLicencesRestore');
            $this->formHelper->setFormActionFromRequest($form, $this->getRequest());
            $view = new ViewModel(['form' => $form]);
            $view->setTemplate('partials/form');
            return $this->render($view);
        }

        if (!$this->isButtonPressed('cancel')) {
            $restore = [
                'licence' => $this->getLicenceId(),
                'communityLicenceIds' => explode(',', $this->params('child_id'))
            ];
            return $this->processDto(RestoreDto::create($restore), 'internal.community_licence.licences_restored');
        }

        return $this->redirectToIndex();
    }

    /**
     * Stop action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function stopAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $form = $this->getStopForm();
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);
            $this->alterStopForm($form);

            if ($form->isValid()) {
                $formattedData = $form->getData();
                $type = $formattedData['data']['type'] === 'N' ? 'withdrawal' : 'suspension';
                $message = ($type === 'withdrawal') ? 'internal.community_licence.licences_withdrawn' :
                    'internal.community_licence.licences_suspended';

                $stop = [
                    'licence' => $this->getLicenceId(),
                    'communityLicenceIds' => explode(',', $this->params('child_id')),
                    'type' => $type,
                    'startDate' =>
                        $formattedData['dates']['startDate'] ?? null,
                    'endDate' =>
                        $formattedData['dates']['endDate'] ?? null,
                    'reasons' => $formattedData['data']['reason']
                ];

                if ($this->lva !== 'licence') {
                    $stop['application'] = $this->getIdentifier();
                }

                return $this->processDto(StopDto::create($stop), $message);
            }
        }

        $this->placeholder()->setPlaceholder('contentTitle', 'Stop community licence');
        $this->scriptFactory->loadFile('community-licence-stop');
        return $this->render($view);
    }

    /**
     * Edit action
     *
     * @return mixed
     */
    public function editAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $form = $this->getEditSuspensionForm();
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        if ($request->isPost()) {
            $data = (array) $request->getPost();
        } else {
            $data = $this->getCommunityLicenceData();
            if ($data instanceof Response) {
                return $data;
            }
        }

        $form->setData($data);
        $this->alterEditSuspensionForm($form);

        if ($request->isPost() && $form->isValid()) {
            $dtoData = CommunityLicMapper::mapFromForm($data, $this->params('child_id'));
            $response = $this->sendCommand(EditSuspensionDto::create($dtoData));
            if ($response->isOk()) {
                $result = $response->getResult();
                if (isset($result['messages']) && count($result['messages'])) {
                    $successMessage = $result['messages'][0];
                    $this->addSuccessMessage($successMessage);
                }

                return $this->redirectToIndex();
            }

            $this->displayErrors($response);
        }

        $this->placeholder()->setPlaceholder('contentTitle', 'Community licence suspension details');

        return $this->render($view);
    }

    /**
     * Get edit suspension form
     *
     * @see \Common\Form\Model\Form\Lva\CommunityLicencesStop
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getEditSuspensionForm()
    {
        $form = $this->formHelper->createForm('Lva\CommunityLicencesEditSuspension');
        $this->formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Alter edit suspension form
     *
     * @param \Laminas\Form\FormInterface $form form
     *
     * @return void
     */
    protected function alterEditSuspensionForm($form)
    {
        $status = $form->get('data')->get('status')->getValue();

        if ($status === RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED) {
            /** @var \Common\Form\Elements\Custom\DateSelect $startDate */
            $startDate = $form->get('dates')->get('startDate');
            $startDate->getDayElement()->setAttribute('readonly', 'readonly');
            $startDate->getMonthElement()->setAttribute('readonly', 'readonly');
            $startDate->getYearElement()->setAttribute('readonly', 'readonly');
            $this->formHelper->removeValidator(
                $form,
                'dates->startDate',
                \Dvsa\Olcs\Transfer\Validators\DateInFuture::class
            );
        }
    }

    /**
     * Get community licence data
     *
     * @return array
     */
    protected function getCommunityLicenceData()
    {
        $response = $this->handleQuery(CommunityLicence::create(['id' => $this->params('child_id')]));

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $result = [];
        if ($response->isOk()) {
            $result = CommunityLicMapper::mapFromResult($response->getResult());
        }

        return $result;
    }

    /**
     * Get stop form @see \Common\Form\Model\Form\Lva\CommunityLicencesStop
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getStopForm()
    {
        $form = $this->formHelper->createForm('Lva\CommunityLicencesStop');
        $this->formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Alter stop form
     *
     * @param \Laminas\Form\FormInterface $form form
     *
     * @return void
     */
    protected function alterStopForm($form)
    {
        if ($form->get('data')->get('type')->getValue() === 'N') {
            $this->formHelper
                ->disableValidation(
                    $form->getInputFilter()->get('dates')->get('startDate')
                );
        }
    }

    /**
     * Action: Reprint
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function reprintAction()
    {
        if ($this->getRequest()->isPost() && $this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        if ($this->getRequest()->isPost()) {
            $reprint = [
                'licence' => $this->getLicenceId(),
                'communityLicenceIds' => explode(',', $this->params('child_id'))
            ];

            if ($this->lva === self::LVA_APP) {
                // If reprinting on an Application with an Interim, then need to pass application ID
                $reprint['application'] = $this->getApplicationId();
            }

            return $this->processDto(ReprintDto::create($reprint), 'internal.community_licence.licences_reprinted');
        }

        return $this->renderConfirmation('internal.community_licence.confirm_reprint_licences');
    }

    /**
     * Render Confirmation
     *
     * @param string $message Message
     *
     * @return \Common\View\Model\Section
     */
    protected function renderConfirmation($message)
    {
        $form = $this->formHelper
            ->createFormWithRequest('GenericConfirmation', $this->getRequest());

        $form->get('messages')->get('message')->setValue($message);

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->render($view);
    }

    /**
     * Process dto
     *
     * @param \Dvsa\Olcs\Transfer\Command\AbstractCommand $dto            dto
     * @param string                                      $successMessage success message
     *
     * @return \Laminas\Http\Response
     */
    protected function processDto($dto, $successMessage)
    {
        /** @var Response $response */
        $response = $this->sendCommand($dto);

        if ($response->isOk()) {
            $this->addSuccessMessage($successMessage);
            return $this->redirectToIndex();
        }

        $this->displayErrors($response);
        return $this->redirectToIndex();
    }

    /**
     * Send command
     *
     * @param \Dvsa\Olcs\Transfer\Command\AbstractCommand $dto dto
     *
     * @return Response
     */
    protected function sendCommand($dto)
    {
        $command = $this->transferAnnotationBuilder->createCommand($dto);
        return $this->commandService->send($command);
    }

    /**
     * Display errors
     *
     * @param Response $response response
     *
     * @return void
     */
    protected function displayErrors($response)
    {
        if ($response->isClientError()) {
            $errors = $response->getResult()['messages'];
            foreach ($errors as $error) {
                $this->addErrorMessage($error);
            }
        }

        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
    }
}
