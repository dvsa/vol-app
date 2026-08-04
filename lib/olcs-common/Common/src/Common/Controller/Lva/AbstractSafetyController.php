<?php

namespace Common\Controller\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\CreateWorkshop as ApplicationCreateWorkshop;
use Dvsa\Olcs\Transfer\Command\Application\UpdateWorkshop as ApplicationUpdateWorkshop;
use Dvsa\Olcs\Transfer\Command\Workshop\CreateWorkshop as LicenceCreateWorkshop;
use Dvsa\Olcs\Transfer\Command\Workshop\UpdateWorkshop as LicenceUpdateWorkshop;
use Dvsa\Olcs\Transfer\Query\Workshop\Workshop;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Safety Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractSafetyController extends AbstractController
{
    use Traits\CrudTableTrait;

    public const DEFAULT_TABLE_RECORDS_COUNT = 10;

    protected $section = 'safety';

    protected string $baseRoute = 'lva-%s/safety';

    /**
     * Shared action data map
     *
     * @var array
     */
    protected $safetyProvidersActionDataMap = [
        '_addresses' => [
            'address'
        ],
        'main' => [
            'children' => [
                'workshop' => [
                    'mapFrom' => [
                        'data'
                    ]
                ],
                'contactDetails' => [
                    'mapFrom' => [
                        'contactDetails'
                    ],
                    'children' => [
                        'addresses' => [
                            'mapFrom' => [
                                'addresses'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    protected $canHaveTrailers;

    protected $isShowTrailers;

    protected $workshops;

    protected $createWorkshopCommandMap = [
        'licence' => LicenceCreateWorkshop::class,
        'application' => ApplicationCreateWorkshop::class,
        'variation' => ApplicationCreateWorkshop::class,
    ];

    protected $updateWorkshopCommandMap = [
        'licence' => LicenceUpdateWorkshop::class,
        'application' => ApplicationUpdateWorkshop::class,
        'variation' => ApplicationUpdateWorkshop::class,
    ];

    protected $safetyData;

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory,
        protected ScriptFactory $scriptFactory,
        protected TranslationHelperService $translationHelper
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Delete Workshops
     *
     * @param array $ids Identifiers
     *
     * @return \Common\Service\Cqrs\Response
     */
    abstract protected function deleteWorkshops($ids);

    /**
     * Save the form data
     *
     * @param array $data    Form Data
     * @param bool  $partial Is partial post
     *
     * @return \Common\Service\Cqrs\Response
     */
    abstract protected function save($data, $partial);

    /**
     * Get Safety Data
     *
     * @param bool $noCache No Cache
     *
     * @return array
     */
    abstract protected function getSafetyData($noCache = false);

    /**
     * Redirect to the first section
     *
     * @return array|\Common\View\Model\Section|Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        // We always want to get the result
        $result = $this->getSafetyData();
        if ($result instanceof ViewModel) {
            return $result;
        }

        $data = $request->isPost() ? (array)$request->getPost() : $this->formatDataForForm($result);

        $form = $this->alterForm($this->getSafetyForm())->setData($data);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$data['table']]);
            $haveCrudAction = ($crudAction !== null);

            if ($crudAction !== null) {
                if ($this->isInternalReadOnly()) {
                    return $this->handleCrudAction($crudAction);
                }

                $this->formHelper->disableEmptyValidation($form);
            }

            if ($form->isValid()) {
                $response = $this->save($data, $crudAction !== null);

                if ($response->isOk()) {
                    if ($crudAction !== null) {
                        return $this->handleCrudAction($crudAction);
                    }

                    return $this->completeSection('safety');
                }

                if ($response->isServerError()) {
                    $this->flashMessengerHelper->addUnknownError();
                } else {
                    $this->mapErrors($form, $response->getResult()['messages']);
                }
            }
        }

        $this->scriptFactory->loadFiles(['vehicle-safety', 'lva-crud']);

        return $this->render('safety', $form);
    }

    /**
     * Map Errors
     *
     * @param Form $form Form
     * @param array         $errors Errors
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['safetyConfirmation'])) {
            foreach ($errors['safetyConfirmation'][0] as $key => $message) {
                $formMessages['application']['safetyConfirmation'][] = $key;
            }

            unset($errors['safetyConfirmation']);
        }

        if (isset($errors['tachographInsName'])) {
            foreach ($errors['tachographInsName'][0] as $key => $message) {
                $formMessages['licence']['tachographInsName'][] = $key;
            }

            unset($errors['tachographInsName']);
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }

    /**
     * Add person action
     *
     * @return \Common\View\Model\Section|Response
     */
    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    /**
     * Edit person action
     *
     * @return \Common\View\Model\Section|Response
     */
    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    /**
     * Delete
     *
     * @return void
     */
    protected function delete()
    {
        $ids = explode(',', $this->params('child_id'));

        $response = $this->deleteWorkshops($ids);

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addUnknownError();
        }
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-safety-inspector';
    }

    /**
     * Helper method as both add and edit pretty
     * much do the same thing
     *
     * @param string $mode Mode
     *
     * @return \Common\View\Model\Section|Response
     */
    protected function addOrEdit($mode)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $safetyProviderData = [];
        $data = [];
        $id = $this->params('child_id');

        if ($mode !== 'add') {
            $dtoParams = [
                $this->getIdentifierIndex() => $this->getIdentifier(),
                'id' => $id
            ];
            $response = $this->handleQuery(Workshop::create($dtoParams));

            if (!$response->isOk()) {
                return $this->notFoundAction();
            }

            $safetyProviderData = $response->getResult();
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $data = $this->formatCrudDataForForm($safetyProviderData, $mode);
        }

        $form = $this->getSafetyProviderForm()->setData($data);
        $this->alterExternalHint($form);

        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        $addressLookup = $this->formHelper->processAddressLookupForm($form, $request);

        if (!$addressLookup && $request->isPost() && $form->isValid()) {
            $dtoData = [
                $this->getIdentifierIndex() => $this->getIdentifier(),
                'isExternal' => $data['data']['isExternal'],
                'contactDetails' => $data['contactDetails']
            ];

            $dtoData['contactDetails']['address'] = $data['address'];

            if ($mode === 'add') {
                $command = $this->createWorkshopCommandMap[$this->lva];
            } else {
                $dtoData['id'] = $id;
                $dtoData['version'] = $data['data']['version'];
                $command = $this->updateWorkshopCommandMap[$this->lva];
            }

            $dto = $command::create($dtoData);

            $response = $this->handleCommand($dto);

            if ($response->isOk()) {
                return $this->handlePostSave(null, ['fragment' => 'table']);
            }

            $this->flashMessengerHelper->addUnknownError();
        }

        $this->scriptFactory->loadFiles(['safety-inspector-add']);

        return $this->render($mode . '_safety', $form);
    }

    /**
     * Alter the hint for the isExternal form element
     *
     * @param Form $form The add/edit form
     *
     * @return void
     */
    protected function alterExternalHint(Form $form)
    {
        $data = null;
        if ($this->lva === 'licence') {
            // load licence data
            $dto = \Dvsa\Olcs\Transfer\Query\Licence\Licence::create(['id' => $this->getIdentifier()]);
        } else {
            // load application/variation data
            $dto = \Dvsa\Olcs\Transfer\Query\Application\Application::create(['id' => $this->getIdentifier()]);
        }

        // load application/variation data
        $response = $this->handleQuery($dto);
        if ($response->isOk()) {
            $data = $response->getResult();

            $ref = $data['niFlag'] . '-' . $data['goodsOrPsv']['id'];
            $links = [
                'N-' . \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE => 'safety-inspector-sample-contract-GV79-GB',
                'N-' . \Common\RefData::LICENCE_CATEGORY_PSV => 'safety-inspector-sample-contract-PSV421-GB',
                'Y-' . \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE => 'safety-inspector-sample-contract-GV79-NI',
            ];

            $hint = $this->translationHelper->translateReplace(
                'safety-inspector-external-hint',
                [$this->url()->fromRoute(
                    'getfile',
                    ['identifier' => base64_encode(
                        $this->translationHelper->translate($links[$ref])
                    )]
                )]
            );

            // Add a hint to the external radio
            /** @var \Laminas\Form\Element\Radio $externalElement */
            $externalElement = $form->get('data')->get('isExternal');
            $externalElement->setOption('hint', $hint);
        }
    }

    /**
     * Format data for the safety providers table
     *
     * @param array  $data Data
     * @param string $mode Mode
     *
     * @return array
     */
    protected function formatCrudDataForForm($data, $mode)
    {
        if ($mode === 'edit') {
            $data['data'] = [
                'version' => $data['version'],
                'isExternal' => $data['isExternal']
            ];

            $data['address'] = $data['contactDetails']['address'];
            $data['address']['countryCode'] = $data['address']['countryCode']['id'];

            unset($data['version']);
            unset($data['isExternal']);
            unset($data['contactDetails']['address']);
        }

        return $data;
    }

    /**
     * Get safety provider form
     *
     * @return \Laminas\Form\Form
     */
    protected function getSafetyProviderForm()
    {
        return $this->formHelper
            ->createFormWithRequest('Lva\SafetyProviders', $this->getRequest());
    }

    /**
     * Alter form
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function alterForm($form)
    {
        /** @var FormHelperService $formHelper */
        $formHelper = $this->formHelper;

        // This element needs to be visible internally
        $formHelper->remove($form, 'application->isMaintenanceSuitable');

        if (!$this->canHaveTrailers) {
            $formHelper->remove($form, 'licence->safetyInsTrailers');

            $formHelper->alterElementLabel(
                $form->get('licence')->get('safetyInsVaries'),
                '.psv',
                FormHelperService::ALTER_LABEL_APPEND
            );

            $table = $form->get('table')->get('table')->getTable();

            $form->get('table')->get('table')->setTable($table);
        } elseif (!$this->isShowTrailers) {
            $formHelper->remove($form, 'licence->safetyInsTrailers');
        }

        $this->alterFormForLva($form);

        return $form;
    }

    /**
     * Format data for form
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function formatDataForForm($data)
    {
        if (isset($data['licence']['tachographIns']['id'])) {
            $data['licence']['tachographIns'] = $data['licence']['tachographIns']['id'];
        }

        $data['application'] = [
            'version' => $data['version'],
            'safetyConfirmation' => $data['safetyConfirmation'],
            'isMaintenanceSuitable' => $data['isMaintenanceSuitable']
        ];

        unset($data['version']);
        unset($data['safetyConfirmation']);
        unset($data['isMaintenanceSuitable']);

        return $data;
    }

    /**
     * Get safety form
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getSafetyForm()
    {
        /** @var \Common\Service\Table\TableBuilder $table */
        $table = $this->tableFactory
            ->prepareTable('lva-safety', $this->workshops, (array) $this->getRequest()->getQuery());

        if ($this->location === 'external') {
            $table->removeColumn('isExternal');
        }

        /** @var \Laminas\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm();

        $this->formHelper
            ->populateFormTable($form->get('table'), $table);

        return $form;
    }
}
