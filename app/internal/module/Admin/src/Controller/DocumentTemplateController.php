<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\DocumentTemplateUpload as DocumentTemplateUploadForm;
use Common\Category;
use Common\Service\AntiVirus\Scan;
use Common\Util\FileContent;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Admin\Form\Model\Form\DocTemplateFilter;
use Zend\Form\Form;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList as ListDTO;
use Dvsa\Olcs\Transfer\Query\DocTemplate\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\DocTemplate\Delete as DeleteDTO;
use Admin\Data\Mapper\DocumentTemplate as DocumentTemplateMapper;

/**
 * Report Upload Controller
 */
class DocumentTemplateController extends AbstractInternalController implements LeftViewProvider
{
    const ERR_UPLOAD_DEF = '4';
    const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

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

    /**
     * Left View setting
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/templates',
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
        $file = (isset($files['fields']['file']) ? $files['fields']['file'] : null);

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $errNr = (isset($file['error']) ? $file['error'] : self::ERR_UPLOAD_DEF);

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
        $scanner = $this->getServiceLocator()->get(Scan::class);
        if ($scanner->isEnabled() && !$scanner->isClean($fileTmpName)) {
            $fileField->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'virus']);
            return $form;
        }

        $mimeType = (isset($file['type']) ? $file['type'] : null);

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

        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');

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
     * @param \Zend\Form\Form $form     Form
     * @param array           $formData Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForAdd($form, $formData)
    {
        $this->getServiceLocator()->get(\Olcs\Service\Data\SubCategory::class)
            ->setCategory(Category::CATEGORY_APPLICATION);

        return $form;
    }

    /**
     * Alter form for editRule action, set default values for listboxes
     *
     * @param \Zend\Form\Form $form     Form
     * @param array           $formData Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForEdit($form, $formData)
    {
        $defaultCategory = isset($formData['fields']['category']) ?
            $formData['fields']['category'] : Category::CATEGORY_APPLICATION;

        $this->getServiceLocator()->get(\Olcs\Service\Data\SubCategory::class)
            ->setCategory($defaultCategory);

        $form->get('fields')->get('templateSlug')->setAttributes(['disabled' => true]);

        return $form;
    }
}
