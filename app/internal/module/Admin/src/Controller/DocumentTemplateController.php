<?php

namespace Admin\Controller;

use Admin\Data\Mapper\DocumentTemplate as DocumentTemplateMapper;
use Admin\Form\Model\Form\DocTemplateFilter;
use Admin\Form\Model\Form\DocumentTemplateUpload as DocumentTemplateUploadForm;
use Common\Category;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Query\DocTemplate\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList as ListDTO;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Service\Data\SubCategory;

class DocumentTemplateController extends AbstractInternalController implements LeftViewProvider
{
    public const ERR_UPLOAD_DEF = '4';
    public const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

    protected $tableName = 'admin-document-templates';
    protected $hasMultiDelete = false;

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;

    protected $formClass = DocumentTemplateUploadForm::class;
    protected $addFormClass = DocumentTemplateUploadForm::class;
    protected $mapperClass = DocumentTemplateMapper::class;
    protected $filterForm = DocTemplateFilter::class;

    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $deleteCommand = DeleteDto::class;


    protected $deleteModalTitle = 'Remove Template';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this template?';
    protected $deleteSuccessMessage = 'The template has been removed';
    protected $addContentTitle = 'Add document template';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/document-template'],
        'editAction' => ['forms/document-template']
    ];

    protected Scan $scannerAntiVirusService;
    protected SubCategory $subCategoryDataService;

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        Scan $scannerAntiVirusService,
        SubCategory $subCategoryDataService
    ) {
        $this->scannerAntiVirusService = $scannerAntiVirusService;
        $this->subCategoryDataService = $subCategoryDataService;

        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }
    /**
     * Left View setting
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/content-management',
                'navigationTitle' => 'Templates'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Redirect to index
     *
     * @return Response
     */
    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/document-templates',
            ['action' => 'index'],
            [],
            true
        );
    }

    /**
     * @return mixed|Form|Response|ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $form = $this->getForm(DocumentTemplateUploadForm::class);
        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $result = $this->processUpload($data, $form, CreateDTO::class);

                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        return parent::addAction();
    }


    /**
     * @return array|Form|Response|ViewModel
     */
    public function editAction()
    {
        $request = $this->getRequest();
        $form = $this->getForm(DocumentTemplateUploadForm::class);
        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $result = $this->processUpload($data, $form, UpdateDTO::class);

                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        return parent::editAction();
    }

    /**
     * Process file uploads
     *
     * @param array $data Form data
     * @param Form  $form Form to display messages
     *
     * @return Form|Response
     */
    private function processUpload(array $data, Form $form, $actionDTO)
    {
        $fileField = $form->get('fields')->get('file');
        $files = $this->getRequest()->getFiles()->toArray();
        $file = ($files['fields']['file'] ?? null);

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $errNr = ($file['error'] ?? self::ERR_UPLOAD_DEF);

            // add validation error message
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . $errNr]);

            return $form;
        }

        $fileTmpName = $file['tmp_name'];

        // eg onAccess anti-virus removed it
        if (!file_exists($fileTmpName)) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'missing']);
            return $form;
        }

        // Run virus scan on file
        $scanner = $this->scannerAntiVirusService;
        if ($scanner->isEnabled() && !$scanner->isClean($fileTmpName)) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'virus']);
            return $form;
        }

        $mimeType = ($file['type'] ?? null);

        $dtoData = [
            'id' => $data['fields']['id'],
            'templateFolder' => $data['fields']['templateFolder'],
            'category' => $data['fields']['category'],
            'subCategory' => $data['fields']['subCategory'],
            'description' => $data['fields']['description'],
            'filename'   => $file['name'],
            'content'    => new FileContent($fileTmpName, $mimeType),
            'suppressFromOp' => $data['fields']['suppressFromOp'],
            'isNi' => $data['fields']['isNi'],
            'templateSlug' => $data['fields']['templateSlug']
        ];

        $response = $this->handleCommand(
            $actionDTO::create($dtoData)
        );

        $flashMessenger = $this->flashMessengerHelperService;

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('Document Template uploaded sucessfully');
            return $this->redirectToIndex();
        } elseif ($response->isClientError()) {
            $messages = $response->getResult()['messages'];
            foreach ($messages as $message) {
                $flashMessenger->addErrorMessage($message);
            }
        }

        return $form;
    }

    /**
     * Alter form for editRule action, set default values for listboxes
     *
     * @param \Laminas\Form\Form $form     Form
     * @param array              $formData Form data
     *
     * @return \Laminas\Form\Form
     */
    protected function alterFormForAdd($form, $formData)
    {
        $this->subCategoryDataService
            ->setCategory(Category::CATEGORY_APPLICATION);

        return $form;
    }

    /**
     * Alter form for editRule action, set default values for listboxes
     *
     * @param \Laminas\Form\Form $form     Form
     * @param array              $formData Form data
     *
     * @return \Laminas\Form\Form
     */
    protected function alterFormForEdit($form, $formData)
    {
        $defaultCategory = $formData['fields']['category'] ?? Category::CATEGORY_APPLICATION;

        $this->subCategoryDataService
            ->setCategory($defaultCategory);

        $form->get('fields')->get('templateSlug')->setAttributes(['disabled' => true]);

        return $form;
    }
}
