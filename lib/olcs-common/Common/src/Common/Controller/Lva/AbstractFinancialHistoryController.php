<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper\Lva\FinancialHistory as FinancialHistoryMapper;
use Common\FormService\FormServiceManager;
use Common\Service\Data\CategoryDataService;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialHistory;
use Dvsa\Olcs\Transfer\Query\Application\FinancialHistory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractFinancialHistoryController extends AbstractController
{
    public $financialHistoryDocuments = [];

    /**
     * Map the data
     *
     * @var array
     */
    protected $dataMap = [
        'main' => [
            'mapFrom' => [
                'data'
            ]
        ]
    ];

    protected FormHelperService $formHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected DataHelperService $dataHelper,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Process Action - Index
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $data = $request->isPost() ? (array)$request->getPost() : $this->getFormData();

        $formParameters = [
            'lva' => $this->lva,
            'niFlag' => $this->getNiFlag($data),
        ];

        /** @var \Common\Form\Form $form */
        $form = $this->getFinancialHistoryForm($formParameters)
            ->setData($data);

        $this->alterFormForLva($form, $data);

        $hasProcessedFiles = $this->processFiles(
            $form,
            'data->file',
            function (array $file): void {
                $this->processFinancialFileUpload($file);
            },
            fn(int $id): bool => $this->deleteFile($id),
            fn(): array => $this->getDocuments()
        );

        if (!$hasProcessedFiles && $request->isPost() && $form->isValid()) {
            $data = $this->dataHelper->processDataMap($data, $this->dataMap);

            if ($this->saveFinancialHistory($form, $data)) {
                return $this->completeSection('financial_history');
            }
        }

        $this->scriptFactory->loadFile('financial-history');

        return $this->render('financial_history', $form);
    }

    /**
     * Get NI Flag for Financial History form
     *
     * @param array $data Data from API or post data
     *
     * @return string
     */
    protected function getNiFlag(array $data)
    {
        if (!isset($data['data']['niFlag'])) {
            return 'N';
        }

        return $data['data']['niFlag'];
    }

    /**
     * Alter form for LVA form
     *
     * @param Form  $form Form
     * @param array $data Api Form Data
     *
     * @return Form
     */
    #[\Override]
    protected function alterFormForLva(Form $form, $data = null)
    {
        return $form;
    }

    /**
     * Get Financial History Form
     *
     * @param array $data Data for form
     *
     * @return FormInterface
     */
    protected function getFinancialHistoryForm(array $data = [])
    {
        return $this->formServiceManager
            ->get('lva-' . $this->lva . '-financial_history')
            ->getForm($this->getRequest(), $data);
    }

    /**
     * Get Form Data
     *
     * @return array
     */
    protected function getFormData()
    {
        $response = $this->getFinancialHistory();

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $mappedResults = [];
        if ($response->isOk()) {
            $mapper = new FinancialHistoryMapper();
            $mappedResults = $mapper->mapFromResult($response->getResult());
            $this->financialHistoryDocuments = $mappedResults['data']['documents'];
        }

        return $mappedResults;
    }

    /**
     * Get Hinancial History
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function getFinancialHistory()
    {
        return $this->handleQuery(FinancialHistory::create(['id' => $this->getIdentifier()]));
    }

    /**
     * Get Documents
     *
     * @return array
     */
    public function getDocuments()
    {
        if (!$this->financialHistoryDocuments) {
            // need this just to populate documents list after upload
            $this->getFormData();
        }

        return $this->financialHistoryDocuments;
    }

    /**
     * Handle the file upload
     *
     * @param array $file File
     */
    public function processFinancialFileUpload($file): void
    {
        $this->uploadFile(
            $file,
            [
                'application' => $this->getApplicationId(),
                'description' => $file['name'],
                'category'    => CategoryDataService::CATEGORY_LICENSING,
                'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL,
                'licence'     => $this->getLicenceId(),
                'isExternal'  => $this->isExternal()
            ]
        );
    }

    /**
     * Save Financial History
     *
     * @param Form  $form     Form
     * @param array $formData Form Data
     *
     * @return bool
     */
    protected function saveFinancialHistory($form, $formData)
    {
        $dtoData = [
            'id' => $this->getIdentifier(),
            'version' => $formData['version'],
            'bankrupt' => $formData['bankrupt'],
            'liquidation' => $formData['liquidation'],
            'receivership' => $formData['receivership'],
            'administration' => $formData['administration'],
            'disqualified' => $formData['disqualified'],
            'insolvencyDetails' => $formData['insolvencyDetails'],
            'insolvencyConfirmation' => $formData['financialHistoryConfirmation']['insolvencyConfirmation'] ?? false
        ];

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(UpdateFinancialHistory::create($dtoData));

        if ($response->isOk()) {
            return true;
        }

        if ($response->isClientError()) {
            $this->mapErrors($form, $response->getResult()['messages']);
            return false;
        }

        $this->flashMessengerHelper->addErrorMessage('unknown-error');
        return false;
    }

    /**
     * Map Errors
     *
     * @param Form  $form   Form
     * @param array $errors Errors
     *
     * @return void
     */
    protected function mapErrors($form, array $errors)
    {
        $formMessages = [];

        $fields = [
            'bankrupt' => 'bankrupt',
            'liquidation' => 'liquidation',
            'receivership' => 'receivership',
            'administration' => 'administration',
            'disqualified' => 'disqualified',
            'insolvencyDetails' => 'insolvencyDetails',
            'insolvencyConfirmation' => 'insolvencyConfirmation'
        ];

        foreach ($fields as $errorKey => $fieldName) {
            if (isset($errors[$errorKey])) {
                foreach ($errors[$errorKey] as $message) {
                    $formMessages['data'][$fieldName][] = $message;
                }

                unset($errors[$errorKey]);
            }
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
