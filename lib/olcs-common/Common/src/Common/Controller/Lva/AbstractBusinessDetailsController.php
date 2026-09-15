<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\Controller\Traits\CompanySearch;
use Common\Data\Mapper\Lva\BusinessDetails as Mapper;
use Common\Data\Mapper\Lva\CompanySubsidiary as CompanySubsidiaryMapper;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query\CompanySubsidiary\CompanySubsidiary;
use Dvsa\Olcs\Transfer\Query\Licence\BusinessDetails;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Shared logic between Business Details Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessDetailsController extends AbstractController
{
    use CrudTableTrait;
    use CompanySearch;

    public const COMPANY_NUMBER_LENGTH = 8;

    protected $section = 'business_details';

    protected string $baseRoute = 'lva-%s/business_details';

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected IdentityProviderInterface $identityProvider,
        protected TableFactory $tableFactory,
        protected FileUploadHelperService $uploadHelper
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Business details section
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $response = $this->handleQuery(BusinessDetails::create(['id' => $this->getLicenceId()]));

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
            return $this->notFoundAction();
        }

        $orgData = $response->getResult();

        $hasOrganisationSubmittedLicenceApplication = $this->identityProvider->getIdentity()->getUserData()['hasOrganisationSubmittedLicenceApplication'] ?? false;

        if ($request->isPost()) {
            $data = $this->getFormPostData($orgData);
        } else {
            $data = Mapper::mapFromResult($orgData);
        }

        // This could replace the BusinessDetails query above but will require a fair amount of refactoring
        $lvaData = $this->fetchDataForLva();

        // Remove option to add subsidiary companies on Psv applications
        $isLicenseApplicationPsv = $lvaData['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV;

        // Gets a fully configured/altered form for any version of this section
        /** @var \Common\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm($orgData['type']['id'], $orgData['hasInforceLicences'], $hasOrganisationSubmittedLicenceApplication, $isLicenseApplicationPsv)
            ->setData($data);
        // need to reset Input Filter defaults after the data has been set on the form
        $form->attachInputFilterDefaults($form->getInputFilter(), $form);

        if ($form->has('table')) {
            $this->populateTable($form, $orgData);
        }

        // Added an early return for non-posts to improve the readability of the code
        if (!$request->isPost()) {
            return $this->renderForm($form);
        }

        $addressFieldset = 'registeredAddress';
        $detailsFieldset = 'data';

        // If we are performing a company number lookup
        if (isset($data['data']['companyNumber']['submit_lookup_company'])) {
            $companyNumber = $data['data']['companyNumber']['company_number'];

            if ($this->isValidCompanyNumber($companyNumber)) {
                $form = $this->populateCompanyDetails(
                    $this->formHelper,
                    $form,
                    $detailsFieldset,
                    $addressFieldset,
                    $companyNumber
                );
            } else {
                $this->formHelper->setInvalidCompanyNumberErrors($form, $detailsFieldset);
            }

            return $this->renderForm($form);
        }

        // We'll re-use this in a few places, so cache the lookup just for the sake of legibility
        $tradingNames = $data['data']['tradingNames'] ?? [];

        // If we are interacting with the trading names collection element
        if (isset($data['data']['submit_add_trading_name'])) {
            $this->processTradingNames($tradingNames, $form);
            return $this->renderForm($form);
        }

        $crudAction = null;

        if (isset($data['table'])) {
            $crudAction = $this->getCrudAction([$data['table']]);
        }

        if ($crudAction !== null) {
            $this->formHelper->disableValidation($form->getInputFilter());
        }

        // If our form is invalid, render the form to display the errors
        if (!$form->isValid()) {
            return $this->renderForm($form);
        }

        if ($this->lva === self::LVA_LIC) {
            $dtoData = [
                'id' => $this->getLicenceId(),
                'version' => $data['version'],
                'name' => $data['data']['name'],
                'tradingNames' => $this->flattenTradingNames($tradingNames),
                'natureOfBusiness' => $data['data']['natureOfBusiness'] ?? null,
                'companyOrLlpNo' => $data['data']['companyNumber']['company_number'] ?? null,
                'registeredAddress' => $data['registeredAddress'] ?? null,
                'partial' => $crudAction !== null,
                'allowEmail' => $data['allow-email']['allowEmail'] ?? null,
            ];

            $response = $this->handleCommand(TransferCmd\Licence\UpdateBusinessDetails::create($dtoData));
        } else {
            $dtoData = [
                'id' => $this->getIdentifier(),
                'licence' => $this->getLicenceId(),
                'version' => $data['version'],
                'name' => $data['data']['name'],
                'tradingNames' => $this->flattenTradingNames($tradingNames),
                'natureOfBusiness' => $data['data']['natureOfBusiness'] ?? null,
                'companyOrLlpNo' => $data['data']['companyNumber']['company_number'] ?? null,
                'registeredAddress' => $data['registeredAddress'] ?? null,
                'partial' => $crudAction !== null
            ];

            $response = $this->handleCommand(TransferCmd\Application\UpdateBusinessDetails::create($dtoData));
        }

        if (!$response->isOk()) {
            $this->mapErrors($form, $response->getResult()['messages']);

            return $this->renderForm($form);
        }

        if ($crudAction !== null) {
            return $this->handleCrudAction($crudAction);
        }

        return $this->completeSection('business_details');
    }

    /**
     * Flatten the array from trading names elements and remove where empty
     *
     * @param array $tradingNames Eg [['name' => 'Trading name 1'], ['name' => ''], ['name' => 'Trading name 2'] ]
     *
     * @return array Eg ['Trading name 1', 'Trading name 2']
     */
    private function flattenTradingNames(array $tradingNames)
    {
        $result = [];
        foreach ($tradingNames as $tradingNameElement) {
            // If name is set (and not empty)
            if (isset($tradingNameElement['name'])) {
                $result[] = $tradingNameElement['name'];
            }
        }

        return $result;
    }

    /**
     * Add Action
     *
     * @return \Common\View\Model\Section
     */
    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    /**
     * Edit action
     *
     * @return \Common\View\Model\Section
     */
    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    /**
     * Method used to render the indexAction form
     *
     * @param \Laminas\Form\Form $form Form
     *
     * @return \Laminas\View\Model\ViewModel
     */
    protected function renderForm($form)
    {
        $this->scriptFactory->loadFiles(['lva-crud']);
        return $this->render('business_details', $form);
    }

    /**
     * Grabs the data from the post, and set's some defaults in-case there are disabled fields
     *
     * @param array $orgData Organisation data
     *
     * @return array
     */
    protected function getFormPostData($orgData)
    {
        $data = (array)$this->getRequest()->getPost();

        if (
            !isset($data['data']['companyNumber'])
            || !array_key_exists('company_number', $data['data']['companyNumber'])
        ) {
            $data['data']['companyNumber']['company_number'] = $orgData['companyOrLlpNo'];
        }

        if (!array_key_exists('name', $data['data'])) {
            $data['data']['name'] = $orgData['name'];
        }

        return $data;
    }

    /**
     * User has pressed 'Add another' on trading names
     * So we need to duplicate the trading names field to produce another input
     *
     * @param array             $tradingNames Trading names
     * @param \Common\Form\Form $form         Form
     *
     * @return void
     */
    protected function processTradingNames($tradingNames, $form)
    {
        $form->setValidationGroup(['data' => ['tradingNames']]);
        if ($form->isValid()) {
            $tradingNames[]['name'] = '';
            $form->get('data')->get('tradingNames')->populateValues($tradingNames);
        }
    }

    /**
     * Add|edit functionality
     *
     * @param string $mode Mode
     *
     * @return \Common\View\Model\Section
     */
    protected function addOrEdit($mode)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $id = $this->params('child_id');

        $data = [];

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $entity = ($this->lva === self::LVA_LIC ? 'licence' : 'application');

            $query = CompanySubsidiary::create(['id' => $id, $entity => $this->getIdentifier()]);

            $response = $this->handleQuery($query);

            if ($response->isClientError()) {
                return $this->notFoundAction();
            }

            $data = CompanySubsidiaryMapper::mapFromResult($response->getResult());
        }

        /** @var \Common\Form\Form $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\BusinessDetailsSubsidiaryCompany', $request)
            ->setData($data);

        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($request->isPost() && $form->isValid()) {
            $dtoData = [
                'name' => $data['data']['name'],
                'companyNo' => $data['data']['companyNo'],
            ];

            // Creating
            $isCreate = ($id === null);

            if (!$isCreate) {
                $dtoData['id'] = $id;
                $dtoData['version'] = $data['data']['version'];
            }

            /** @var QueryInterface $dtoClass */
            if ($this->lva === self::LVA_LIC) {
                $dtoData['licence'] = $this->getIdentifier();

                if ($isCreate) {
                    $dtoClass = TransferCmd\Licence\CreateCompanySubsidiary::class;
                } else {
                    $dtoClass = TransferCmd\Licence\UpdateCompanySubsidiary::class;
                }
            } else {
                $dtoData['application'] = $this->getIdentifier();

                if ($isCreate) {
                    $dtoClass = TransferCmd\Application\CreateCompanySubsidiary::class;
                } else {
                    $dtoClass = TransferCmd\Application\UpdateCompanySubsidiary::class;
                }
            }

            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave(null, ['fragment' => 'table']);
            }

            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return $this->render($mode . '_subsidiary_company', $form);
    }

    /**
     * Populate tables
     *
     * @param \Common\Form\Form $form    Form
     * @param array             $orgData Data
     *
     * @return void
     */
    protected function populateTable($form, $orgData)
    {
        $table = $this->tableFactory
            ->prepareTable('lva-subsidiaries', $orgData['companySubsidiaries']);

        $this->formHelper->populateFormTable($form->get('table'), $table);
    }

    /**
     * Mechanism to *actually* delete a subsidiary, invoked by the underlying delete action
     *
     * @return boolean
     */
    protected function delete()
    {
        $data = [
            'ids' => explode(',', $this->params('child_id')),
            $this->getIdentifierIndex() => $this->getIdentifier(),
        ];

        /** @var QueryInterface $dtoClass */
        if ($this->lva === self::LVA_LIC) {
            $dtoClass = TransferCmd\Licence\DeleteCompanySubsidiary::class;
        } else {
            $dtoClass = TransferCmd\Application\DeleteCompanySubsidiary::class;
        }

        $response = $this->handleCommand($dtoClass::create($data));

        return $response->isOk();
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-company-subsidiary';
    }

    /**
     * Map errors
     *
     * @param Form  $form   Form
     * @param array $errors Errors
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['natureOfBusiness'])) {
            $formMessages['data']['natureOfBusiness'] = $errors['natureOfBusiness'];
            unset($errors['natureOfBusiness']);
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }

    private function isValidCompanyNumber($companyNumber): bool
    {
        return strlen($companyNumber) == self::COMPANY_NUMBER_LENGTH;
    }
}
