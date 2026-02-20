<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterAppendix as LetterAppendixMapper;
use Admin\Form\Model\Form\Letter\LetterAppendix as LetterAppendixForm;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\Document\Upload as DocumentUploadDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterAppendix\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterAppendix\GetList as ListDTO;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterAppendixController extends AbstractInternalController implements LeftViewProvider
{
    public const ERR_UPLOAD_DEF = '4';
    public const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

    protected $tableName = 'admin-letter-appendix';
    protected $defaultTableSortField = 'appendixKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterAppendixForm::class;
    protected $addFormClass = LetterAppendixForm::class;
    protected $mapperClass = LetterAppendixMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Appendix';
    protected $editContentTitle = 'Edit Letter Appendix (Creates New Version)';

    protected $deleteModalTitle = 'Remove Letter Appendix';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter appendix?';
    protected $deleteSuccessMessage = 'The letter appendix has been removed';

    protected $addSuccessMessage = 'Letter appendix created successfully';
    protected $editSuccessMessage = 'Letter appendix updated successfully (new version created)';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/letter-appendix'],
        'editAction' => ['forms/letter-appendix'],
    ];

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected Scan $scannerAntiVirusService
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }

    /**
     * @return mixed
     */
    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = $this->getForm(LetterAppendixForm::class);
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $result = $this->processSubmit($data, $form, CreateDTO::class);

                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        return parent::addAction();
    }

    /**
     * @return mixed
     */
    public function editAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = $this->getForm(LetterAppendixForm::class);
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $result = $this->processSubmit($data, $form, UpdateDTO::class);

                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        return parent::editAction();
    }

    /**
     * Process form submission, handling file upload for PDF type
     *
     * @param array $data Form data
     * @param Form $form Form instance
     * @param string $actionDTO Command DTO class
     * @return Form|Response
     */
    private function processSubmit(array $data, Form $form, string $actionDTO)
    {
        $formData = $data['letterAppendix'] ?? [];
        $appendixType = $formData['appendixType'] ?? 'pdf';

        // Build base command data
        $commandData = LetterAppendixMapper::mapFromForm($data);

        // For PDF type, handle file upload
        if ($appendixType === 'pdf') {
            $documentId = $this->handleFileUpload($form);

            if ($documentId === null) {
                // File upload failed - form already has error messages
                return $form;
            }

            if ($documentId !== false) {
                // New file was uploaded, set document ID
                $commandData['document'] = $documentId;
            }
            // If $documentId === false, no file was provided (optional on edit)
        }

        // Add id and version for update operations
        if ($actionDTO === UpdateDTO::class) {
            $commandData['id'] = $this->params()->fromRoute('id');
            $commandData['version'] = $formData['version'] ?? 1;
        }

        $response = $this->handleCommand($actionDTO::create($commandData));

        $flashMessenger = $this->flashMessengerHelperService;

        if ($response->isOk()) {
            $message = ($actionDTO === CreateDTO::class)
                ? $this->addSuccessMessage
                : $this->editSuccessMessage;
            $flashMessenger->addSuccessMessage($message);
            return $this->redirectToIndex();
        }

        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'] ?? [];
            foreach ($messages as $message) {
                $flashMessenger->addErrorMessage($message);
            }
        }

        return $form;
    }

    /**
     * Handle file upload for PDF appendices
     *
     * @param Form $form Form instance for error messages
     * @return int|false|null Document ID on success, false if no file uploaded, null on error
     */
    private function handleFileUpload(Form $form)
    {
        $files = $this->getRequest()->getFiles()->toArray();
        $file = $files['letterAppendix']['document'] ?? null;

        // No file uploaded - this is OK on edit (keep existing document)
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        $fileField = $form->get('letterAppendix')->get('document');

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . ($file['error'] ?? self::ERR_UPLOAD_DEF)]);
            return null;
        }

        $fileTmpName = $file['tmp_name'];

        if (!file_exists($fileTmpName)) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'missing']);
            return null;
        }

        // Antivirus scan
        $scanner = $this->scannerAntiVirusService;
        if ($scanner->isEnabled() && !$scanner->isClean($fileTmpName)) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'virus']);
            return null;
        }

        $mimeType = $file['type'] ?? 'application/pdf';

        // Upload the file via Document\Upload command
        $uploadResponse = $this->handleCommand(
            DocumentUploadDTO::create([
                'filename' => $file['name'],
                'content' => new FileContent($fileTmpName, $mimeType),
                'category' => \Common\Category::CATEGORY_SYSTEM,
                'subCategory' => \Common\Category::DOC_SUB_CATEGORY_LETTER_APPENDIX,
                'description' => 'Letter appendix PDF',
            ])
        );

        if (!$uploadResponse->isOk()) {
            $fileField->setMessages(['Failed to upload document']);
            return null;
        }

        $result = $uploadResponse->getResult();
        return $result['id']['document'] ?? null;
    }

    /**
     * Redirect to index
     *
     * @return Response
     */
    public function redirectToIndex(): Response
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-letter-appendix',
            ['action' => 'index'],
            [],
            true
        );
    }

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/letter-management',
                'navigationTitle' => 'Letter Management',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
