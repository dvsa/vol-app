<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper\Lva\FinancialEvidence;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\View\Helper\ReturnToAddress;
use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialEvidence;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Financial Evidence Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractFinancialEvidenceController extends AbstractController
{
    protected FormHelperService $formHelper;

    protected GuidanceHelperService $guidanceHelper;

    /**
     * @param $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected TableFactory $tableFactory,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected CommandService $commandService,
        protected $lvaAdapter,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Application create : Financial evidence section
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $id      = $this->getIdentifier();
        /** @var \Common\Controller\Lva\Adapters\AbstractFinancialEvidenceAdapter $adapter */
        $adapter = $this->lvaAdapter;

        // get data
        if ($request->isPost()) {
            $formData = FinancialEvidence::mapFromPost((array)$request->getPost());
        } else {
            $formData = FinancialEvidence::mapFromResult($adapter->getData($id));
        }

        // set up form
        /** @var \Common\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-financial_evidence')
            ->getForm($this->getRequest())
            ->setData($formData);

        $this->alterFormForLva($form);
        $adapter->alterFormForLva($form);

        // handle files
        $hasProcessedFiles = $this->processFiles(
            $form,
            'evidence->files',
            function (array $file): void {
                $this->processFinancialEvidenceFileUpload($file);
            },
            fn(int $id): bool => $this->deleteFile($id),
            fn(): array => $this->getDocuments(),
            'evidence->uploadedFileCount'
        );

        // update application record and redirect
        if (!$hasProcessedFiles && $request->isPost() && $form->isValid() && $this->saveFinancialEvidence($formData)) {
            return $this->completeSection('financial_evidence');
        }

        // load scripts
        $this->scriptFactory->loadFiles(['financial-evidence']);

        // render view
        $lvaData = $adapter->getData($id);

        $variables = $lvaData['financialEvidence'] +
            [
                'applicationReference' => $lvaData['applicationReference'],
                'sendToAddress' => ReturnToAddress::getAddress($this->isNi($lvaData), '</br>'),
            ];

        return $this->render('financial_evidence', $form, $variables);
    }

    /**
     * Callback to handle the file upload
     *
     * @param array $file File data
     *
     * @throws \Common\Exception\File\InvalidMimeException
     * @throws \Exception
     */
    public function processFinancialEvidenceFileUpload($file): void
    {
        /** @var \Common\Controller\Lva\Adapters\AbstractFinancialEvidenceAdapter $adapter */
        $adapter = $this->lvaAdapter;

        $id = $this->getIdentifier();

        $data = array_merge(
            $adapter->getUploadMetaData($file, $id),
            [
                'isExternal' => $this->isExternal()
            ]
        );

        $this->uploadFile($file, $data);

        // force reload of data with new document included
        $adapter->getData($id, true);
    }

    /**
     * Callback to get list of documents
     *
     * @return array
     */
    public function getDocuments()
    {
        /** @var \Common\Controller\Lva\Adapters\AbstractFinancialEvidenceAdapter $adapter */
        $adapter = $this->lvaAdapter;

        return $adapter->getDocuments(
            $this->getIdentifier()
        );
    }

    /**
     * Save financial evidence
     *
     * @param array $formData Form Data
     *
     * @return bool
     */
    protected function saveFinancialEvidence($formData)
    {
        $dto = UpdateFinancialEvidence::create(FinancialEvidence::mapFromForm($formData));

        $command = $this->transferAnnotationBuilder->createCommand($dto);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->commandService->send($command);

        if ($response->isOk()) {
            return true;
        }

        $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
        return false;
    }

    /**
     * Define is Nothern Ireland application
     *
     * @param array $lvaData LVA object data
     *
     * @return bool
     */
    private function isNi(array $lvaData)
    {
        if (isset($lvaData['niFlag'])) {
            return ValueHelper::isOn($lvaData['niFlag']);
        }

        if (isset($lvaData['trafficArea']['isNi'])) {
            return (bool)$lvaData['trafficArea']['isNi'];
        }

        if (isset($lvaData['licence']['trafficArea']['isNi'])) {
            return (bool)$lvaData['licence']['trafficArea']['isNi'];
        }

        return false;
    }
}
