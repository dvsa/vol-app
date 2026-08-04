<?php

namespace Common\Controller\Lva;

use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Query\Licence\PsvDiscs;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractDiscsController extends AbstractController
{
    use Traits\CrudTableTrait;

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'discs';

    protected string $baseRoute = 'lva-%s/discs';

    protected $formTableData;

    protected $spacesRemaining;

    //  actions
    public const ACTION_CEASED_SHOW_HIDE = 'ceased-show-hide';

    // Command keys
    public const CMD_REQUEST_DISCS = 'requested';

    public const CMD_VOID_DISCS = 'voided';

    public const CMD_REPLACE_DISCS = 'replaced';

    protected $commandMap = [
        self::CMD_REQUEST_DISCS => [
            'licence' => \Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs::class,
            'variation' => \Dvsa\Olcs\Transfer\Command\Variation\CreatePsvDiscs::class
        ],
        self::CMD_VOID_DISCS => [
            'licence' => \Dvsa\Olcs\Transfer\Command\Licence\VoidPsvDiscs::class,
            'variation' => \Dvsa\Olcs\Transfer\Command\Variation\VoidPsvDiscs::class
        ],
        self::CMD_REPLACE_DISCS => [
            'licence' => \Dvsa\Olcs\Transfer\Command\Licence\ReplacePsvDiscs::class,
            'variation' => \Dvsa\Olcs\Transfer\Command\Variation\ReplacePsvDiscs::class
        ]
    ];

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected TableFactory $tableFactory,
        protected GuidanceHelperService $guidanceHelper,
        protected ScriptFactory $scriptFactory
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Process action Index
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $data = [];
        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $crudAction = $this->getCrudAction([$data['table']]);
            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction, ['add', self::ACTION_CEASED_SHOW_HIDE]);
            }

            return $this->completeSection('discs');
        }

        $form = $this->getDiscsForm();
        if ($form === null) {
            return $this->notFoundAction();
        }

        $form->setData($data);

        /** @var \Common\Service\Script\ScriptFactory $scriptSrv */
        $scriptSrv = $this->scriptFactory;
        $scriptSrv->loadFiles(['forms/filter']);
        $scriptSrv->loadFiles(['lva-crud', 'more-actions']);

        if ((int)$this->spacesRemaining < 0) {
            $this->guidanceHelper->append('more-discs-than-authorisation');
        }

        $params = [
            'mainWrapperCssClass' => 'full-width',
        ];

        return $this->render('discs', $form, $params);
    }

    /**
     * Process action Add
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $formHelper = $this->formHelper;

        /** @var \Common\Form\Form $form */
        $form = $formHelper->createForm('Lva\PsvDiscsRequest');
        $form->get('form-actions')->remove('addAnother');

        $formHelper->setFormActionFromRequest($form, $request);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        }

        if ($request->isPost() && $form->isValid()) {
            $response = $this->processRequestDiscs($form->getData());

            $flashMssgrHelper = $this->flashMessengerHelper;

            if ($response->isOk()) {
                $flashMssgrHelper->addSuccessMessage('psv-discs-' . self::CMD_REQUEST_DISCS . '-successfully');

                return $this->redirect()->toRouteAjax(
                    $this->getBaseRoute(),
                    [$this->getIdentifierIndex() => $this->getIdentifier()]
                );
            }

            if ($response->isServerError()) {
                $flashMssgrHelper->addCurrentErrorMessage('unknown-error');
            } else {
                $this->mapErrors($form, $response->getResult()['messages']);
            }
        }

        return $this->render('add_discs', $form);
    }

    /**
     * Make request to API to create a disks
     *
     * @param array $data Command parameters
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function processRequestDiscs($data)
    {
        $amount = $data['data']['additionalDiscs'];

        $dtoData = [
            $this->getIdentifierIndex() => $this->getIdentifier(),
            'amount' => $amount
        ];

        /** @var AbstractCommand $commandClass */
        $commandClass = $this->commandMap[self::CMD_REQUEST_DISCS][$this->lva];
        return $this->handleCommand($commandClass::create($dtoData));
    }

    /**
     * Process action Replace
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function replaceAction()
    {
        return $this->commonConfirmCommand(self::CMD_REPLACE_DISCS);
    }

    /**
     * Process action Void
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function voidAction()
    {
        return $this->commonConfirmCommand(self::CMD_VOID_DISCS);
    }

    /**
     * Hidden action. Used to change status of visibility of ceased discs in table
     *
     * @return \Laminas\Http\Response
     */
    public function ceasedShowHideAction()
    {
        $isIncluded = ($this->params()->fromQuery('includeCeased', 0) === '1');

        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'index'],
            ['query' => ($isIncluded ? [] : ['includeCeased' => '1'])],
            true
        );
    }

    /**
     * Get Table of disks form
     */
    protected function getDiscsForm()
    {
        $formHelper = $this->formHelper;

        /** @var \Common\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm();

        $discTable = $this->getDiscsTable();
        if ($discTable === null) {
            return null;
        }

        $formHelper->populateFormTable($form->get('table'), $discTable);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $form;
    }

    /**
     * Get disks table
     */
    protected function getDiscsTable()
    {
        $tableParams = $this->getFilters();
        $tableParams['query'] = $tableParams;

        $tableData = $this->getTableData();
        if ($tableData === null) {
            return null;
        }

        $table = $this->tableFactory->prepareTable(
            'lva-psv-discs',
            $tableData,
            $tableParams
        );

        return $this->alterTable($table, $tableParams);
    }

    /**
     * Get table parameters
     *
     * @return array
     */
    private function getFilters()
    {
        $params = $this->params();

        return [
            'includeCeased' => $params->fromQuery('includeCeased', 0),
            'limit' => $params->fromQuery('limit', 10),
            'page' => $params->fromQuery('page', 1),
        ];
    }

    /**
     * Make call to Api to get data of Table of Disks
     *
     * @return array|null
     */
    protected function getTableData()
    {
        if ($this->formTableData === null) {
            $data = $this->getFilters();
            $data['id'] = $this->getLicenceId();

            $response = $this->handleQuery(PsvDiscs::create($data));
            if ($response->isForbidden()) {
                return null;
            }

            $result = $response->getResult();
            $data = $result['psvDiscs'];
            $this->spacesRemaining = $result['remainingSpacesPsv'];

            $this->formTableData = [];

            foreach ($data as $disc) {
                $disc['discNo'] = $this->getDiscNumberFromDisc($disc);
                $this->formTableData[] = $disc;
            }

            $this->formTableData = [
                'results' => $this->formTableData,
                'count' => $result['totalPsvDiscs'],
            ];
        }

        return $this->formTableData;
    }

    /**
     * Format disk number
     *
     * @param array $disc Disk data
     *
     * @return string
     */
    protected function getDiscNumberFromDisc($disc)
    {
        if (isset($disc['discNo'])) {
            return $disc['discNo'];
        }
        if (!empty($disc['issuedDate'])) {
            return '';
        }
        if (!empty($disc['ceasedDate'])) {
            return '';
        }
        return 'Pending';
    }

    /**
     * Map errors
     *
     * @param Form  $form   Form
     * @param array $errors Error messages
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        if (isset($errors['amount']['LIC-PSVDISC-1'])) {
            $form->setMessages(
                [
                    'data' => [
                        'additionalDiscs' => [
                            'additional-psv-discs-validator-too-many'
                        ]
                    ]
                ]
            );
            unset($errors['amount']);
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }
    }

    /**
     * Show confirmation form and execute command by key
     *
     * @param string $commandKey Name of command
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    protected function commonConfirmCommand($commandKey)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            //  save
            $dtoData = [
                $this->getIdentifierIndex() => $this->getIdentifier(),
                'ids' => explode(',', $this->params('child_id')),
            ];
            $this->commonCommand($commandKey, $dtoData);

            return $this->redirect()->toRouteAjax(
                $this->getBaseRoute(),
                [$this->getIdentifierIndex() => $this->getIdentifier()],
                ['query' => $request->getQuery()->toArray()]
            );
        }

        $form = $this->formHelper->createFormWithRequest('GenericConfirmation', $this->getRequest());

        return $this->render($commandKey . '_discs', $form);
    }

    /**
     * Execute command by key with specified parameters
     *
     * @param string $commandKey Name of command
     * @param array  $dtoData    Command parameters
     *
     * @return void
     */
    protected function commonCommand($commandKey, array $dtoData)
    {
        /** @var AbstractCommand $commandClass */
        $commandClass = $this->commandMap[$commandKey][$this->lva];
        $response = $this->handleCommand($commandClass::create($dtoData));

        $flashMssgrHelper = $this->flashMessengerHelper;

        if ($response->isOk()) {
            $flashMssgrHelper->addSuccessMessage('psv-discs-' . $commandKey . '-successfully');
        } else {
            $flashMssgrHelper->addErrorMessage('unknown-error');
        }
    }

    /**
     * Set additional setting for table
     *
     * @param TableBuilder $table   Table
     * @param array        $filters Table parameters
     *
     * @return TableBuilder
     */
    private function alterTable(TableBuilder $table, array $filters = [])
    {
        $isIncluded = (isset($filters['includeCeased']) && $filters['includeCeased'] === '1');

        $table->addAction(
            self::ACTION_CEASED_SHOW_HIDE,
            [
                'label' => 'internal-psv-discs-filter-ceased-' . ($isIncluded ? 'hide' : 'show') . '-discs',
                'class' => 'govuk-button govuk-button--secondary js-disable-crud',
                'requireRows' => true,
                'keepForReadOnly' => true,
            ]
        );

        if ($this->fetchDataForLva()['licenceType']['id'] !== RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $table->setEmptyMessage("");
        }

        return $table;
    }
}
