<?php

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Admin\Form\Model\Form\ReportUpload as ReportUploadForm;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\Report\Upload;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class ReportUploadController extends AbstractInternalController implements LeftViewProvider
{
    use ReportLeftViewTrait;

    public const ERR_UPLOAD_DEF = '4';
    public const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['forms/report-upload'],
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
     * Action: index
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $form = $this->getForm(ReportUploadForm::class);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $result = $this->processUpload($data, $form);
                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        $this->setPageTitle();
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Set the page title
     *
     * @return void
     */
    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Upload reports');
    }

    /**
     * Redirect to index
     *
     * @return Response
     */
    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-report/upload',
            ['action' => 'index'],
            [],
            true
        );
    }

    /**
     * Process file uploads
     *
     * @param array $data Form data
     * @param Form  $form Form to display messages
     *
     * @return Form|Response
     */
    private function processUpload(array $data, Form $form)
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

        $response = $this->handleCommand(
            Upload::create(
                [
                    'reportType'    => $data['fields']['reportType'],
                    'filename'      => $file['name'],
                    'content'       => new FileContent($fileTmpName, $mimeType),
                    'name'          => $data['fields']['emailTemplate'],
                    'templateSlug'  => $data['fields']['docTemplate'],
                ]
            )
        );

        $flashMessenger = $this->flashMessengerHelperService;

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('Report uploaded successfully.');

            return $this->redirectToIndex();
        } elseif ($response->isClientError()) {
            $messages = $response->getResult()['messages'];

            if (isset($messages['ERR_MIME'])) {
                $fileField->setMessages(['ERR_MIME']);

                return $form;
            }
        }

        $flashMessenger->addCurrentUnknownError();

        return $form;
    }
}
