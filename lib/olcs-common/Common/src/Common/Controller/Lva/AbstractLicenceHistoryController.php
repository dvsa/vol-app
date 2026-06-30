<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper\Lva\LicenceHistory as LicenceHistoryMapper;
use Common\Data\Mapper\Lva\OtherLicence as OtherLicenceMapper;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateLicenceHistory;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateOtherLicence;
use Dvsa\Olcs\Transfer\Command\OtherLicence\DeleteOtherLicence;
use Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateOtherLicence;
use Dvsa\Olcs\Transfer\Query\Application\LicenceHistory;
use Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Filter\Word\CamelCaseToDash;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence History Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractLicenceHistoryController extends AbstractController
{
    use Traits\CrudTableTrait;

    protected $sections = [
        'guidance' => [],
        'data' => [
            'prevHasLicence',
            'prevHadLicence',
            'prevBeenDisqualifiedTc',
        ],
        'eu' => [
            'prevBeenRefused',
            'prevBeenRevoked',
        ],
        'pi' => [
            'prevBeenAtPi',
        ],
        'assets' => [
            'prevPurchasedAssets'
        ]
    ];

    protected $section = 'licence_history';

    protected string $baseRoute = 'lva-%s/licence_history';

    protected $otherLicences = [];

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected StringHelperService $stringHelper,
        protected TableFactory $tableFactory,
        FormHelperService $formHelper
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $data = $request->isPost() ? (array)$request->getPost() : $this->formatDataForForm($this->getFormData());

        $form = $this->getLicenceHistoryForm()->setData($data);

        $this->alterFormForLva($form);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction($data);

            $inProgress = false;
            if ($crudAction !== null) {
                $inProgress = true;
                $this->formHelper->disableEmptyValidation($form);
            }

            if ($form->isValid() && $this->saveLicenceHistory($form, $data, $inProgress)) {
                if ($crudAction !== null) {
                    return $this->handleCrudAction($crudAction);
                }

                return $this->completeSection('licence_history');
            }
        }

        $this->scriptFactory->loadFiles(['lva-crud', 'licence-history']);

        return $this->render('licence_history', $form);
    }

    /**
     * Override the get crud action method
     *
     * @return array
     */
    protected function getCrudAction(array $formTables = [])
    {
        $data = $formTables;

        $filter = new CamelCaseToDash();

        foreach ($this->sections as $group => $sections) {
            foreach ($sections as $section) {
                if (isset($data[$group][$section . '-table']['action'])) {
                    $action = $this->getActionFromCrudAction($data[$group][$section . '-table']);

                    $data[$group][$section . '-table']['routeAction'] = sprintf(
                        '%s-%s',
                        $filter->filter($section),
                        strtolower($action)
                    );

                    return $data[$group][$section . '-table'];
                }
            }
        }

        return null;
    }

    protected function delete(): bool
    {
        $saveData = [
            'ids' => explode(',', $this->params('child_id'))
        ];

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(DeleteOtherLicence::create($saveData));
        if ($response->isOk()) {
            return true;
        }

        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return false;
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-other-licence';
    }

    protected function saveLicenceHistory($form, $data, $inProgress): bool
    {
        $data = $this->formatDataForSave($data);

        $data['id'] = $this->getApplicationId();
        $data['inProgress'] = $inProgress;

        $response = $this->handleCommand(UpdateLicenceHistory::create($data));

        if ($response->isOk()) {
            return true;
        }

        if ($response->isClientError()) {
            $this->mapErrorsForLicenceHistory($form, $response->getResult()['messages']);
        }

        if ($response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return false;
    }

    /**
     * @param \Common\Form\Form $form
     */
    protected function mapErrorsForLicenceHistory($form, array $errors): void
    {
        $formMessages = [];

        foreach ($this->sections as $group => $sections) {
            foreach ($sections as $section) {
                if (isset($errors[$section])) {
                    foreach ($errors[$section] as $message) {
                        $formMessages[$group][$section][] = $message;
                    }

                    unset($errors[$section]);
                }
            }
        }

        $fm = $this->flashMessengerHelper;
        foreach ($errors as $error) {
            $fm->addCurrentErrorMessage($error);
        }

        $form->setMessages($formMessages);
    }

    /**
     * @psalm-return array{version: mixed,...}
     */
    protected function formatDataForSave($data): array
    {
        $saveData = [];

        foreach ($this->sections as $group => $sections) {
            foreach ($sections as $section) {
                if (isset($data[$group][$section])) {
                    $saveData[$section] = $data[$group][$section];
                }
            }
        }

        $saveData['version'] = $data['version'];

        return $saveData;
    }

    protected function getFormData()
    {
        $response = $this->getLicenceHistory();

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $mappedResults = [];

        if ($response->isOk()) {
            $mappedResults = LicenceHistoryMapper::mapFromResult($response->getResult());
            $this->otherLicences = $mappedResults['data']['otherLicences'];
        }

        return $mappedResults;
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    protected function getLicenceHistory()
    {
        return $this->handleQuery(LicenceHistory::create(['id' => $this->getIdentifier()]));
    }

    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{version: mixed,...}
     */
    protected function formatDataForForm($data): array
    {
        $data = $data['data'];
        $formData = [];

        foreach ($this->sections as $group => $sections) {
            foreach ($sections as $section) {
                $formData[$group][$section] = $data[$section];
            }
        }

        $formData['version'] = $data['version'];

        return $formData;
    }

    /**
     * Get Licence History Form
     *
     * @return \Common\Form\Form
     */
    protected function getLicenceHistoryForm()
    {
        $formHelper = $this->formHelper;

        /** @var \Common\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm();

        $form->get('questionsHint')
            ->get('message')->setAttribute('value', "markup-application_previous-history_licence-history_data");

        foreach ($this->sections as $group => $sections) {
            foreach ($sections as $section) {
                $formHelper->populateFormTable(
                    $form->get($group)->get($section . '-table'),
                    $this->getTable($section),
                    $group . '[' . $section . '-table]'
                );
            }
        }

        return $form;
    }

    protected function getTable($which)
    {
        return $this->tableFactory
            ->prepareTable('lva-licence-history-' . $which, $this->getTableData($which));
    }

    protected function getTableData($which)
    {
        if (count($this->otherLicences) === 0) {
            $this->getFormData();
        }

        return $this->otherLicences[$which];
    }

    protected function getLicenceTypeFromSection($section)
    {
        return $this->stringHelper->camelToUnderscore($section);
    }

    /**
     * Add prevHasLicence licence
     *
     */
    public function prevHasLicenceAddAction()
    {
        $this->scriptFactory->loadFiles(['add-licence-history']);
        return $this->addOrEdit('add', 'prevHasLicence');
    }

    /**
     * Edit prevHasLicence licence
     *
     */
    public function prevHasLicenceEditAction()
    {
        return $this->addOrEdit('edit', 'prevHasLicence');
    }

    /**
     * Delete prevHasLicence licence
     */
    public function prevHasLicenceDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevHadLicence licence
     *
     */
    public function prevHadLicenceAddAction()
    {
        return $this->addOrEdit('add', 'prevHadLicence');
    }

    /**
     * Edit prevHadLicence licence
     *
     */
    public function prevHadLicenceEditAction()
    {
        return $this->addOrEdit('edit', 'prevHadLicence');
    }

    /**
     * Delete prevHadLicence licence
     */
    public function prevHadLicenceDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevBeenRefused licence
     *
     */
    public function prevBeenRefusedAddAction()
    {
        return $this->addOrEdit('add', 'prevBeenRefused');
    }

    /**
     * Edit prevBeenRefused licence
     *
     */
    public function prevBeenRefusedEditAction()
    {
        return $this->addOrEdit('edit', 'prevBeenRefused');
    }

    /**
     * Delete refused licence
     */
    public function prevBeenRefusedDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevBeenRevoked licence
     *
     */
    public function prevBeenRevokedAddAction()
    {
        return $this->addOrEdit('add', 'prevBeenRevoked');
    }

    /**
     * Edit prevBeenRevoked licence
     *
     */
    public function prevBeenRevokedEditAction()
    {
        return $this->addOrEdit('edit', 'prevBeenRevoked');
    }

    /**
     * Delete prevBeenRevoked licence
     */
    public function prevBeenRevokedDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevBeenDisqualifiedTc licence
     *
     */
    public function prevBeenDisqualifiedTcAddAction()
    {
        return $this->addOrEdit('add', 'prevBeenDisqualifiedTc');
    }

    /**
     * Edit prevBeenDisqualifiedTc licence
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    public function prevBeenDisqualifiedTcEditAction()
    {
        return $this->addOrEdit('edit', 'prevBeenDisqualifiedTc');
    }

    /**
     * Delete prevBeenDisqualifiedTc licence
     *
     * @return \Laminas\Http\Response
     */
    public function prevBeenDisqualifiedTcDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevPurchasedAssets licence
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    public function prevPurchasedAssetsAddAction()
    {
        return $this->addOrEdit('add', 'prevPurchasedAssets');
    }

    /**
     * Edit prevPurchasedAssets licence
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    public function prevPurchasedAssetsEditAction()
    {
        return $this->addOrEdit('edit', 'prevPurchasedAssets');
    }

    /**
     * Delete prevPurchasedAssets licence
     *
     * @return \Laminas\Http\Response
     */
    public function prevPurchasedAssetsDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Add prevBeenAtPi licence
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    public function prevBeenAtPiAddAction()
    {
        return $this->addOrEdit('add', 'prevBeenAtPi');
    }

    /**
     * Edit prevBeenAtPi licence
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    public function publicInquiryEditAction()
    {
        return $this->addOrEdit('edit', 'prevBeenAtPi');
    }

    /**
     * Delete prevBeenAtPi licence
     *
     * @return \Laminas\Http\Response
     */
    public function prevBeenAtPiDeleteAction()
    {
        return $this->deleteAction();
    }

    /**
     * Generic functionality for adding/editing
     *
     * @param string $mode  Operation
     * @param string $which Which part to operate
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    protected function addOrEdit($mode, $which)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $data = [];
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $id = $this->params('child_id');

            $data = $this->getLicenceFormData($id);

            // If the loaded previous licence type doesn't match the one we are editing
            if ($data['previousLicenceType']['id'] !== $this->getLicenceTypeFromSection($which)) {
                return $this->notFoundAction();
            }
        }

        if (!$request->isPost()) {
            $data = $this->formatDataForLicenceForm($data, $which);
        }

        /** @var \Common\Form\Form $form */
        $form = $this->alterActionForm($this->getLicenceForm(), $which)->setData($data);

        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($request->isPost() && $form->isValid()) {
            $this->saveLicence($form, $form->getData());

            return $this->handlePostSave(
                (new CamelCaseToDash())->filter($which),
                ['fragment' => $which]
            );
        }

        return $this->render($mode . '_licence_history', $form);
    }

    /**
     * Get licence form data
     *
     * @param int $id Id
     *
     * @return array
     */
    protected function getLicenceFormData($id)
    {
        $response = $this->getOtherLicenceData($id);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $mappedResults = [];
        if ($response->isOk()) {
            $mappedResults = OtherLicenceMapper::mapFromResult($response->getResult());
        }

        return $mappedResults;
    }

    /**
     * Get Other Licence Data
     *
     * @param int $id Id
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function getOtherLicenceData($id)
    {
        return $this->handleQuery(OtherLicence::create(['id' => $id]));
    }

    /**
     * Get the altered licence form
     *
     * @return \Laminas\Form\Form
     */
    protected function getLicenceForm()
    {
        return $this->formHelper
            ->createFormWithRequest('Lva\LicenceHistoryLicence', $this->getRequest());
    }

    /**
     * Alter the form based on the licence type
     *
     * @param \Common\Form\Form $form  Form
     * @param string            $which Which part to operate
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function alterActionForm($form, $which)
    {
        $formHelper = $this->formHelper;

        if ($which !== 'prevBeenDisqualifiedTc') {
            $formHelper->remove($form, 'data->disqualificationDate');
            $formHelper->remove($form, 'data->disqualificationLength');
        }

        if ($which !== 'prevHasLicence') {
            $formHelper->remove($form, 'data->willSurrender');
            $formHelper->remove($form, 'data->willSurrenderMessage');
        }

        if ($which !== 'prevPurchasedAssets') {
            $formHelper->remove($form, 'data->purchaseDate');
        }

        return $form;
    }

    /**
     * Process action load
     *
     * @param array  $data  Data
     * @param string $which Which part to operate
     *
     * @return array
     */
    protected function formatDataForLicenceForm($data, $which)
    {
        $data['previousLicenceType'] = $this->getLicenceTypeFromSection($which);

        return ['data' => $data];
    }

    /**
     * Save licence
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form Data
     *
     * @return bool
     */
    protected function saveLicence($form, $formData)
    {
        $saveData = $formData['data'];
        $saveData['id'] = $this->params('child_id');
        $saveData['application'] = $this->getApplicationId();

        if (empty($saveData['id'])) {
            $dto = CreateOtherLicence::create($saveData);
        } else {
            $dto = UpdateOtherLicence::create($saveData);
        }

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            return true;
        }

        if ($response->isClientError()) {
            $fields = [
                'licNo' => 'licNo',
                'holderName' => 'holderName',
                'willSurrender' => 'willSurrender',
                'disqualificationDate' => 'disqualificationDate',
                'disqualificationLength' => 'disqualificationLength',
                'purchaseDate' => 'purchaseDate',
            ];
            $this->mapErrors($form, $response->getResult()['messages'], $fields, 'data');
        }

        if ($response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return false;
    }

    protected function mapErrors($form, array $errors, array $fields = [], $fieldsetName = ''): void
    {
        $formMessages = [];

        foreach ($fields as $errorKey => $fieldName) {
            if (isset($errors[$errorKey])) {
                foreach ($errors[$errorKey] as $message) {
                    $formMessages[$fieldsetName][$fieldName][] = $message;
                }

                unset($errors[$errorKey]);
            }
        }

        $fm = $this->flashMessengerHelper;
        if (!empty($errors['application'])) {
            $fm->addCurrentErrorMessage($errors['application']);
        } elseif ($errors !== []) {
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
