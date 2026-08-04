<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Traits\TransferVehiclesTrait;
use Common\Controller\Lva\Traits\VehicleSearchTrait;
use Common\Data\Mapper;
use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\CreateGoodsVehicle as ApplicationCreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Application\DeleteGoodsVehicle as ApplicationDeleteGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Application\UpdateGoodsVehicle as ApplicationUpdateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicles as AppUpdateVehicles;
use Dvsa\Olcs\Transfer\Command\Licence\CreateGoodsVehicle as LicenceCreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles as LicUpdateVehicles;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle as LicenceDeleteLicenceVehicle;
use Dvsa\Olcs\Transfer\Command\Vehicle\ReprintDisc;
use Dvsa\Olcs\Transfer\Command\Vehicle\UpdateGoodsVehicle as LicenceUpdateGoodsVehicle;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehicle;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\FormInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Goods Vehicles
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGoodsVehiclesController extends AbstractController
{
    use TransferVehiclesTrait;
    use VehicleSearchTrait;
    use Traits\CrudTableTrait {
        handleCrudAction as protected traitHandleCrudAction;
    }

    public const DEF_TABLE_FIRST_PAGE_NR = 1;

    public const DEF_TABLE_ITEMS_COUNT = 25;

    public const SEARCH_VEHICLES_COUNT = 20;

    protected $section = 'vehicles';

    protected string $baseRoute = 'lva-%s/vehicles';

    protected $totalAuthorisedVehicles = [];

    protected $totalVehicles = [];

    protected $loadDataMap = [
        'licence' => TransferQry\Licence\GoodsVehicles::class,
        'variation' => TransferQry\Variation\GoodsVehicles::class,
        'application' => TransferQry\Application\GoodsVehicles::class,
    ];

    protected $createVehicleMap = [
        'licence' => LicenceCreateGoodsVehicle::class,
        'variation' => ApplicationCreateGoodsVehicle::class,
        'application' => ApplicationCreateGoodsVehicle::class
    ];

    protected $updateVehicleMap = [
        'licence' => LicenceUpdateGoodsVehicle::class,
        'variation' => ApplicationUpdateGoodsVehicle::class,
        'application' => ApplicationUpdateGoodsVehicle::class
    ];

    protected $deleteVehicleMap = [
        'licence' => LicenceDeleteLicenceVehicle::class,
        'variation' => ApplicationDeleteGoodsVehicle::class,
        'application' => ApplicationDeleteGoodsVehicle::class
    ];

    protected $headerData;

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    protected FormServiceManager $formServiceManager;

    protected TranslationHelperService $translationHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        protected TableFactory $tableFactory,
        protected GuidanceHelperService $guidanceHelper,
        TranslationHelperService $translationHelper,
        protected ScriptFactory $scriptFactory,
        protected VariationLvaService $variationLvaService,
        protected GoodsVehiclesVehicle $goodsVehiclesVehicleMapper
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formServiceManager = $formServiceManager;
        $this->translationHelper = $translationHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Additional functionality for action
     *
     * @param string $action Action
     *
     * @return \Laminas\Http\Response
     */
    protected function checkForAlternativeCrudAction($action)
    {
        return null;
    }

    /**
     * Process Index action
     *
     * @return \Common\View\Model\Section|null|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $headerData = $this->getHeaderData();
        if ($headerData === null) {
            return $this->notFoundAction();
        }

        $formData = [];

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } elseif ($this->lva === 'application') {
            $formData = Mapper\Lva\GoodsVehicles::mapFromResult($headerData);
        } elseif ($this->lva === 'licence') {
            $formData = Mapper\Lva\LicenceGoodsVehicles::mapFromResult($headerData);
        }

        $formData = array_merge($formData, ['query' => (array)$request->getQuery()]);
        $form = $this->getForm($headerData, $formData);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$formData['table']]);

            if ($crudAction !== null && $this->isInternalReadOnly()) {
                return $this->handleCrudAction($crudAction);
            }

            if ($form->isValid()) {
                $response = $this->updateVehiclesSection($form, ($crudAction !== null), $headerData);
                if ($response !== null) {
                    return $response;
                }

                if ($crudAction !== null) {
                    return $this->handleCrudAction($crudAction);
                }

                return $this->completeSection('vehicles');
            }
        }

        return $this->renderForm($form, $headerData);
    }

    /**
     * Override handleCrudAction
     *
     * @param array $crudActionData Action data
     *
     * @return \Laminas\Http\Response
     */
    protected function handleCrudAction(array $crudActionData)
    {
        $alternativeCrudResponse = $this->checkForAlternativeCrudAction(
            $this->getActionFromCrudAction($crudActionData)
        );
        if ($alternativeCrudResponse !== null) {
            return $alternativeCrudResponse;
        }

        return $this->traitHandleCrudAction(
            $crudActionData,
            [
                'add', 'print-vehicles', 'export', 'show-removed-vehicles', 'hide-removed-vehicles'
            ]
        );
    }

    /**
     * Update Vehicle Section
     *
     * @param \Common\Form\Form $form           Form
     * @param string            $haveCrudAction Action
     * @param array             $headerData     Data from db
     *
     * @return \Common\View\Model\Section|null
     */
    protected function updateVehiclesSection(FormInterface $form, $haveCrudAction, $headerData)
    {
        if ($this->lva === 'application') {
            $data = $form->getData()['data'];

            $dtoData = [
                'id' => $this->getIdentifier(),
                'version' => $data['version'],
                'hasEnteredReg' => $data['hasEnteredReg'],
                'partial' => $haveCrudAction
            ];

            $response = $this->handleCommand(AppUpdateVehicles::create($dtoData));

            if ($response->isServerError()) {
                $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
                return $this->renderForm($form, $headerData);
            }

            if ($response->isClientError()) {
                $this->mapErrors($form, $response->getResult()['messages']);
                return $this->renderForm($form, $headerData);
            }
        }

        if ($this->lva === 'licence') {
            $shareInfo = $form->getData()['shareInfo']['shareInfo'];

            $dtoData = [
                'id' => $this->getIdentifier(),
                'shareInfo' => $shareInfo
            ];

            $response = $this->handleCommand(LicUpdateVehicles::create($dtoData));

            if (!$response->isOk()) {
                $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
                return $this->renderForm($form, $headerData);
            }
        }

        return null;
    }

    /**
     * Request data from API
     *
     * @return array|null
     */
    protected function getHeaderData()
    {
        if ($this->headerData === null) {
            $dtoData = $this->getFilters();
            $dtoData['id'] = $this->getIdentifier();

            $dtoClass = $this->loadDataMap[$this->lva];

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->handleQuery($dtoClass::create($dtoData));
            if ($response->isForbidden()) {
                return null;
            }

            $this->headerData = $response->getResult();
        }

        return $this->headerData;
    }

    /**
     * Process Add action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $result = $this->getVehicleSectionData();

        if ($result['spacesRemaining'] < 1) {
            if ($this->lva === 'variation' || $this->lva === 'application') {
                $message = $this->translationHelper
                    ->translateReplace(
                        'markup-more-vehicles-than-total-auth-error-variation',
                        [
                            $result['totAuthVehicles'],
                            $this->url()->fromRoute(
                                'lva-' . $this->lva .
                                '/operating_centres',
                                ['action' => null],
                                [],
                                true
                            )
                        ]
                    );
            } else {
                $message = $this->translationHelper
                    ->translateReplace(
                        'markup-more-vehicles-than-total-auth-error',
                        [
                            $result['totAuthVehicles'],
                            $this->variationLvaService->getVariationLink($this->getLicenceId(), 'operating_centres')
                        ]
                    );
            }

            $this->flashMessengerHelper
                ->addProminentErrorMessage($message);

            return $this->redirect()->toRouteAjax(
                $this->getBaseRoute(),
                ['action' => null],
                ['query' => $request->getQuery()->toArray()],
                true
            );
        }

        $data = [];

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        $params = [];
        $params['spacesRemaining'] = $result['spacesRemaining'];

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-goods-vehicles-add-vehicle')
            ->getForm($this->getRequest(), $params)
            ->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            $dtoData = [
                'id' => $this->getIdentifier(),
                'vrm' => $formData['data']['vrm'],
                'unvalidatedVrm' => $formData['data']['unvalidatedVrm'] ?? null,
                'platedWeight' => $formData['data']['platedWeight'],
                'receivedDate' => $formData['licence-vehicle']['receivedDate'] ?? null,
                'specifiedDate' => $formData['licence-vehicle']['specifiedDate'] ?? null,
                'confirm' => $data['licence-vehicle']['confirm-add'] ?? null
            ];

            $dtoClass = $this->createVehicleMap[$this->lva];
            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave(null, ['query' => $request->getQuery()->toArray()]);
            }

            if ($response->isServerError()) {
                $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
            } else {
                $messages = $response->getResult()['messages'];

                if (isset($messages['VE-VRM-2'])) {
                    $confirm = new Checkbox(
                        'confirm-add',
                        ['label' => 'vehicle-belongs-to-another-licence-confirmation']
                    );

                    $confirm->setMessages([$this->formatConfirmationMessage($messages['VE-VRM-2'])]);

                    $form->get('licence-vehicle')->add($confirm);
                } else {
                    $this->mapVehicleErrors($form, $messages);
                }
            }
        }

        return $this->render('add_vehicles', $form);
    }

    /**
     * Process Edit Action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $id = $this->params('child_id');

        $response = $this->handleQuery(LicenceVehicle::create(['id' => $id]));

        $vehicleData = $response->getResult();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->goodsVehiclesVehicleMapper->mapFromResult($vehicleData);
        }

        $params = [
            'isRemoved' => !is_null($vehicleData['removalDate'])
        ];

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-goods-vehicles-edit-vehicle')
            ->getForm($this->getRequest(), $params)
            ->setData($data);

        if ($vehicleData['showHistory']) {
            $this->formHelper->populateFormTable(
                $form->get('vehicle-history-table'),
                $this->tableFactory->prepareTable('lva-vehicles-history', $vehicleData['history'])
            );
        }

        if (!is_null($vehicleData['removalDate'])) {
            $this->formHelper
                ->disableValidation($form->getInputFilter());
        }

        // If the vehicle is removed, ignore validation
        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            // Is removed
            if (!is_null($vehicleData['removalDate'])) {
                $dtoData = [
                    $this->getIdentifierIndex() => $this->getIdentifier(),
                    'id' => $id,
                    'version' => $formData['data']['version'],
                    'removalDate' => $formData['licence-vehicle']['removalDate'] ?? null,
                ];

                $dtoClass = $this->updateVehicleMap[$this->lva];

                $response = $this->handleCommand($dtoClass::create($dtoData));
            } else {
                $dtoData = [
                    $this->getIdentifierIndex() => $this->getIdentifier(),
                    'id' => $id,
                    'version' => $formData['data']['version'],
                    'platedWeight' => $formData['data']['platedWeight'],
                    'receivedDate' => $formData['licence-vehicle']['receivedDate'] ?? null,
                    'specifiedDate' => $formData['licence-vehicle']['specifiedDate'] ?? null,
                    'seedDate' => $formData['licence-vehicle']['warningLetterSeedDate'] ?? null,
                    'sentDate' => $formData['licence-vehicle']['warningLetterSentDate'] ?? null,
                ];

                $dtoClass = $this->updateVehicleMap[$this->lva];

                $response = $this->handleCommand($dtoClass::create($dtoData));
            }

            if ($response->isOk()) {
                return $this->handlePostSave(null, ['query' => $request->getQuery()->toArray()]);
            }

            if ($response->isServerError()) {
                $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
            } else {
                $this->mapVehicleErrors($form, $response->getResult()['messages']);
            }
        }

        return $this->render('edit_vehicles', $form);
    }

    /**
     * Delete vehicles
     *
     * @return bool
     */
    protected function delete()
    {
        $ids = explode(',', $this->params('child_id'));

        $dtoData = [
            $this->getIdentifierIndex() => $this->getIdentifier(),
            'ids' => $ids
        ];

        $dtoClass = $this->deleteVehicleMap[$this->lva];

        $response = $this->handleCommand($dtoClass::create($dtoData));

        return $response->isOk();
    }

    /**
     * Get the delete message.
     *
     * @return string
     */
    public function getDeleteMessage()
    {
        $toDelete = count(explode(',', $this->params('child_id')));

        $result = $this->getVehicleSectionData();

        $acceptedLicenceTypes = [
            RefData::LICENCE_TYPE_STANDARD_NATIONAL,
            RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        if (!in_array($result['licenceType']['id'], $acceptedLicenceTypes, false)) {
            return 'delete.confirmation.text';
        }

        if ($result['activeVehicleCount'] > $toDelete) {
            return 'delete.confirmation.text';
        }

        return 'deleting.all.vehicles.message';
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-vehicles';
    }

    /**
     * Process Reprint action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function reprintAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $ids = explode(',', $this->params('child_id'));

            $response = $this->handleCommand(ReprintDisc::create(['ids' => $ids]));

            if (!$response->isOk()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->redirect()->toRouteAjax(
                $this->getBaseRoute(),
                [
                    $this->getIdentifierIndex() => $this->getIdentifier()
                ],
                [
                    'query' => $request->getQuery()->toArray()
                ],
                true
            );
        }

        $form = $this->getConfirmationForm($request);

        return $this->render('reprint_vehicles', $form);
    }

    /**
     * Transfer vehicles action
     *
     * @return mixed
     */
    public function transferAction()
    {
        return $this->transferVehicles();
    }

    /**
     * Render Form
     *
     * @param \Common\Form\Form $form       Form
     * @param array             $headerData Data from Api
     *
     * @return \Common\View\Model\Section
     */
    protected function renderForm($form, $headerData)
    {
        if ($headerData['spacesRemaining'] < 0) {
            $this->guidanceHelper->append('more-vehicles-than-authorisation');
        }

        $files = $this->getScripts();
        $params = [
            'mainWrapperCssClass' => 'full-width',
        ];

        $searchForm = $this->getVehcileSearchForm($headerData);
        if ($searchForm) {
            $params['searchForm'] = $searchForm;
            $files[] = 'forms/vehicle-search';
        }

        $this->scriptFactory->loadFiles($files);

        return $this->render('vehicles', $form, $params);
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'lva-crud', 'vehicle-goods'}
     */
    protected function getScripts(): array
    {
        return ['lva-crud', 'vehicle-goods'];
    }

    /**
     * Build table
     *
     * @param array $headerData Data from Api
     * @param array $filters    Route parameters
     *
     * @return mixed
     */
    protected function getTable($headerData, $filters)
    {
        $query = $this->removeUnusedParametersFromQuery(
            (array)$this->getRequest()->getQuery()
        );
        $params = array_merge($query, ['query' => $query]);

        $tableName = 'lva-' . $this->location . '-vehicles';

        $table = $this->tableFactory
            ->prepareTable($tableName, $headerData['licenceVehicles'], $params);

        $this->makeTableAlterations($table, $headerData, $filters);

        return $table;
    }

    /**
     * Make table alter
     *
     * @param TableBuilder $table   Table
     * @param array        $params  Changes parameters
     * @param array        $filters Route parameters
     *
     * @return void
     */
    protected function makeTableAlterations(TableBuilder $table, $params, $filters)
    {
        if (isset($params['canReprint']) && $params['canReprint']) {
            $table->addAction(
                'reprint',
                [
                    'label' => 'vehicle_table_action.reprint.label',
                    'requireRows' => true,
                    'class' => ' more-actions__item govuk-button govuk-button--secondary',
                ]
            );
        }

        if (isset($params['canTransfer']) && $params['canTransfer']) {
            $table->addAction(
                'transfer',
                [
                    'label' => 'vehicle_table_action.transfer.label',
                    'class' => ' more-actions__item js-require--multiple govuk-button govuk-button--secondary',
                    'requireRows' => true,
                ]
            );
        }

        if (isset($params['canExport']) && $params['canExport']) {
            $table->addAction(
                'export',
                [
                    'label' => 'vehicle_table_action.export.label',
                    'requireRows' => true,
                    'class' => ' more-actions__item js-disable-crud govuk-button govuk-button--secondary',
                ]
            );
        }

        if (isset($params['canPrintVehicle']) && $params['canPrintVehicle']) {
            $table->addAction(
                'print-vehicles',
                [
                    'label' => 'vehicle_table_action.print-vehicles.label',
                    'requireRows' => true,
                    'class' => ' more-actions__item govuk-button govuk-button--secondary',
                ]
            );
        }
        if (!isset($params['allVehicleCount'])) {
            return;
        }
        if (!isset($params['activeVehicleCount'])) {
            return;
        }
        if ((int)$params['allVehicleCount'] <= (int)$params['activeVehicleCount']) {
            return;
        }
        $this->addRemovedVehiclesActions($filters, $table);
    }

    /**
     * Show confirmation
     *
     * @param \Laminas\Http\Request $request Htt Request
     *
     * @return mixed
     */
    protected function getConfirmationForm(\Laminas\Http\Request $request)
    {
        return $this->formHelper
            ->createFormWithRequest('GenericConfirmation', $request);
    }

    /**
     * Define filters (query/route parameters)
     *
     * @return array
     */
    protected function getFilters()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $query = $request->isPost() ? $request->getPost('query') : $request->getQuery();

        return $this->formatFilters((array)$query);
    }

    /**
     * Format filters (query/route parameters)
     *
     * @param array $query parameters
     *
     * @return array
     */
    protected function formatFilters($query)
    {
        $filters = [
            'page'  => ($query['page'] ?? self::DEF_TABLE_FIRST_PAGE_NR),
            'limit' => ($query['limit'] ?? self::DEF_TABLE_ITEMS_COUNT),
            'sort'  => $query['sort'] ?? 'createdOn',
            'order' => $query['order'] ?? 'DESC',
        ];

        if (isset($query['vehicleSearch']['vrm']) && !isset($query['vehicleSearch']['clearSearch'])) {
            $filters['vrm'] = $query['vehicleSearch']['vrm'];
        }

        if (isset($query['specified']) && in_array($query['specified'], ['Y', 'N'], false)) {
            $filters['specified'] = $query['specified'];
        }

        $filters['includeRemoved'] = (isset($query['includeRemoved']) && $query['includeRemoved'] == '1');

        if (isset($query['disc']) && in_array($query['disc'], ['Y', 'N'], false)) {
            $filters['disc'] = $query['disc'];
        }

        return $filters;
    }

    /**
     * Get pre-configured form
     *
     * @param array $headerData Data from Api
     * @param array $formData   Data from Post
     *
     * @return mixed
     */
    protected function getForm($headerData, $formData)
    {
        return $this->formServiceManager
            ->get('lva-' . $this->lva . '-goods-' . $this->section)
            ->getForm($this->getTable($headerData, $this->getFilters()))
            ->setData($formData);
    }

    /**
     * Map errors
     *
     * @param FormInterface $form   Form
     * @param array         $errors Error messages
     *
     * @return void
     */
    protected function mapErrors(\Laminas\Form\FormInterface $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['vehicles'])) {
            foreach ($errors['vehicles'] as $key => $message) {
                $formMessages['table']['table'][] = $key;
            }

            unset($errors['vehicles']);
        }

        $form->setMessages($formMessages);

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }
    }

    /**
     * Map vehicle errors
     *
     * @param FormInterface $form   Form
     * @param array         $errors Error messages
     *
     * @return void
     */
    protected function mapVehicleErrors(\Laminas\Form\FormInterface $form, array $errors)
    {
        $errors = Mapper\Lva\GoodsVehiclesVehicle::mapFromErrors($errors, $form);

        if (!empty($errors)) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }
    }

    /**
     * Format the confirmation message
     *
     * @param string $message Json message
     *
     * @return string
     */
    protected function formatConfirmationMessage($message)
    {
        $decoded = json_decode($message);

        if (is_array($decoded)) {
            $message = 'vehicle-belongs-to-another-licence-message-internal';

            if (count($decoded) > 1) {
                $message .= '-multiple';
            }

            return $this->translationHelper->translateReplace($message, [implode(', ', $decoded)]);
        }

        return $this->translationHelper->translate('vehicle-belongs-to-another-licence-message-external');
    }

    /**
     * Get vehicle section data
     *
     * @return mixed
     */
    protected function getVehicleSectionData()
    {
        $dtoData = [
            'id'    => $this->getIdentifier(),
            'page'  => 1,
            'limit' => 1,
            'sort'  => 'createdOn',
            'order' => 'DESC'
        ];
        $dtoClass = $this->loadDataMap[$this->lva];
        $response = $this->handleQuery($dtoClass::create($dtoData));

        return $response->getResult();
    }
}
