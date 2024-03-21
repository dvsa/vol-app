<?php

namespace Olcs\Controller\Document;

use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Laminas\Form\Form;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Service\Data\DocumentSubCategory;

class DocumentUploadController extends AbstractDocumentController
{
    public const ERR_UPLOAD_DEF = '4';
    public const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected DocumentSubCategory $documentSubcategoryDataService;
    protected Scan $avScanner;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        array $config,
        FlashMessengerHelperService $flashMessengerHelper,
        DocumentSubCategory $docSubcategoryDataService,
        Scan $avScanner
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->documentSubcategoryDataService = $docSubcategoryDataService;
        $this->avScanner = $avScanner;
    }

    /**
     * Upload action
     *
     * @return ViewModel
     */
    public function uploadAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $category = $data['details']['category'];
        } else {
            $type = $this->params()->fromRoute('type');
            $category = $this->getCategoryForType($type);
            $data = [
                'details' => [
                    'category' => $category
                ]
            ];
        }

        //  set dynamic select
        $this->documentSubcategoryDataService
            ->setCategory($category);

        $form = $this->generateFormWithData('UploadDocument', [$this, 'processUpload'], $data);

        $this->loadScripts(['upload-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('pages/form');
        return $this->renderView($view, 'Upload document');
    }

    /**
     * Process file uploads
     *
     * @param array $data Form data
     * @param Form  $form Form to display messages
     *
     * @return Form|\Laminas\Http\Response
     */
    public function processUpload($data, Form $form)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        $files = $this->getRequest()->getFiles()->toArray();

        $file = ($files['details']['file'] ?? null);

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $errNr = ($file['error'] ?? self::ERR_UPLOAD_DEF);

            // add validation error message to element, with reason upload errored
            $form->get('details')->get('file')->setMessages([self::FILE_UPLOAD_ERR_PREFIX . $errNr]);

            return null;
        }

        $fileTmpName = $file['tmp_name'];

        // eg onAccess anti-virus removed it
        if (!file_exists($fileTmpName)) {
            $form->get('details')->get('file')->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'missing']);

            return null;
        }

        // Run virus scan on file
        $scanner = $this->avScanner;
        if ($scanner->isEnabled() && !$scanner->isClean($fileTmpName)) {
            $form->get('details')->get('file')->setMessages([self::FILE_UPLOAD_ERR_PREFIX . 'virus']);

            return null;
        }

        $mimeType = ($file['type'] ?? null);

        $data = array_merge(
            $data,
            [
                'filename'      => $file['name'],
                'content'       => new FileContent($fileTmpName, $mimeType),
                'description'   => $data['details']['description'],
                'category'      => $data['details']['category'],
                'subCategory'   => $data['details']['documentSubCategory'],
                'isExternal'    => false,
            ]
        );

        $key = $this->getRouteParamKeyForType($type);
        $data[$type] = $routeParams[$key];

        // we need to link certain documents to multiple IDs
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getLicenceIdForApplication();
                break;

            case 'case':
                $data = array_merge(
                    $data,
                    $this->getCaseData()
                );
                break;

            case 'busReg':
                $data['licence'] = $routeParams['licence'];
                break;

            case 'irhpApplication':
                $data['licence'] = $routeParams['licence'];
                break;

            default:
                break;
        }

        $response = $this->handleCommand(Upload::create($data));

        if ($response->isOk()) {
            $identifier = $response->getResult()['id']['identifier'];

            $routeParams = array_merge(
                $routeParams,
                [
                    'doc' => $identifier
                ]
            );

            return $this->redirectToDocumentRoute($type, null, $routeParams);
        }

        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];

            if (isset($messages['ERR_MIME'])) {
                $formMessages = [
                    'details' => [
                        'file' => [
                            'ERR_MIME'
                        ]
                    ]
                ];

                $form->setMessages($formMessages);

                return $form;
            }
        }

        $this->flashMessengerHelper->addCurrentUnknownError();

        return $form;
    }
}
