<?php

namespace Olcs\Controller\Document;

use Common\Category;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter as PrintLetterCmd;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

class DocumentFinaliseController extends AbstractDocumentController
{
    public const PRINT_MSGS_SUCCESS = [
        'close' => 'The document has been saved',
        PrintLetterCmd::METHOD_EMAIL => 'The document has been saved and sent by email',
        PrintLetterCmd::METHOD_PRINT_AND_POST => 'The document has been saved, printed and sent by post',
        'proposeToRevoke' => 'The document has been saved and sent by post and email'
    ];

    private $redirect;
    private FlashMessengerHelperService $flashMessengerHelper;
    private WebDavJsonWebTokenGenerationService $webDavJsonWebTokenGenerationService;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        array $config,
        FlashMessengerHelperService $flashMessengerHelper,
        WebDavJsonWebTokenGenerationService $webDavJsonWebTokenGenerationService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->webDavJsonWebTokenGenerationService = $webDavJsonWebTokenGenerationService;
    }

    /**
     * finalise Action
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function finaliseAction()
    {
        $routeParams = $this->params()->fromRoute();

        if ($this->isButtonPressed('back')) {
            return $this->redirectToDocumentRoute($routeParams['type'], 'generate', $routeParams);
        }

        if ($this->isButtonPressed('cancelFinalise')) {
            return $this->redirect()->toRoute(
                null,
                ['action' => 'cancel'],
                ['query' => $this->getRequest()->getQuery()->toArray()],
                true
            );
        }

        $data = $this->fetchDocData();

        $category = $data['data']['category']['description'];
        $documentSubCategory = $data['data']['subCategory']['subCategoryName'];

        $jwt = $this->webDavJsonWebTokenGenerationService->generateToken(
            'intusr',
            $data['data']['identifier']
        );

        $url = $this->webDavJsonWebTokenGenerationService->getJwtWebDavLink(
            $jwt,
            $data['data']['identifier'],
        );

        $link = sprintf(
            '<a class="govuk-link" href="%s" data-file-url="%s" target="_blank">%s</a>',
            htmlentities($url, ENT_QUOTES, 'utf-8'),
            htmlentities($url, ENT_QUOTES, 'utf-8'),
            htmlentities($data['data']['template']['description'], ENT_QUOTES, 'utf-8')
        );

        $data = [
            'category' => htmlspecialchars($category, ENT_QUOTES, 'utf-8'),
            'subCategory' => htmlspecialchars($documentSubCategory, ENT_QUOTES, 'utf-8'),
            'template' => $link,
        ];

        $form = $this->generateFormWithData('FinaliseDocument', 'processSaveLetter', $data, false);

        foreach ($data as $key => $value) {
            $form->get($key)->setAttribute('value', $value);
        }

        if ($this->redirect !== null) {
            return $this->redirect;
        }

        $this->scriptFactory->loadFile('file-link');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');
        return $this->renderView($view, 'Amend letter');
    }

    /**
     * print Action
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function printAction()
    {
        $docId = $this->params('doc');

        /**
         * @var \Laminas\Http\Request $request
         */
        $request = $this->getRequest();

        //  POST: process form
        $respPost = null;

        if ($request->isPost()) {
            $method = null;
            if ($this->isButtonPressed('email')) {
                $method = PrintLetterCmd::METHOD_EMAIL;
            } elseif ($this->isButtonPressed('printAndPost')) {
                $method = PrintLetterCmd::METHOD_PRINT_AND_POST;
            } elseif ($this->isButtonPressed('proposeToRevoke')) {
                return $this->processProposeToRevoke();
            } else {
                $this->flashMessengerHelper->addSuccessMessage(self::PRINT_MSGS_SUCCESS['close']);

                return $this->handleRedirectToDocumentRoute(true);
            }

            $respPost = $this->handleCommand(
                PrintLetterCmd::create(
                    [
                        'id' => $docId,
                        'method' => $method,
                    ]
                )
            );

            if ($respPost->isOk()) {
                $this->flashMessengerHelper->addSuccessMessage(self::PRINT_MSGS_SUCCESS[$method]);

                return $this->handleRedirectToDocumentRoute($request->isXmlHttpRequest());
            }
        }

        //  GET: build form
        $respGet = $this->handleQuery(
            TransferQry\Document\PrintLetter::create(['id' => $docId])
        );

        $result = $respGet->getResult();

        //  POST & GET: process API error
        foreach ([$respGet, $respPost] as $resp) {
            if ($resp === null) {
                continue;
            }

            if ($resp->isServerError()) {
                $this->flashMessengerHelper->addUnknownError();
            } elseif ($resp->isClientError()) {
                $respResult = $resp->getResult();
                $errMsgs = ($respResult['messages'] ?? []);
                foreach ($errMsgs as $err) {
                    $this->flashMessengerHelper->addCurrentErrorMessage($err);
                }
            }
        }

        //  prepare form
        $form = $this->formHelper->createForm('DocumentSend');

        //  define visibility of buttons
        $flags = $result['flags'];
        if (!$flags[PrintLetterCmd::METHOD_EMAIL]) {
            $this->formHelper->disableElement($form, 'form-actions->email');
        }

        if (!$flags[PrintLetterCmd::METHOD_PRINT_AND_POST]) {
            $this->formHelper->disableElement($form, 'form-actions->printAndPost');
        }

        $labelText = 'Would you like to send this letter?';
        $subText = '';

        if ($this->isProposeToRevoke()) {
            $this->modifyFormForProposeToRevoke($form);
            $labelText = 'Select \'Propose to revoke\' to send this letter to the correspondence address and relevant email addresses.';
            $subText = 'Select \'Close\' to save the letter without sending.';
        }

        $view = new ViewModel(
            [
                'form' => $form,
                'label' => $labelText,
                'subText' => $subText
            ]
        );
        $view->setTemplate('pages/confirm');

        return $this->renderView($view, 'Send letter');
    }

    /**
     * Cancel Action
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function cancelAction()
    {
        if ($this->getRequest()->isPost()) {
            $queryParams = $this->getRequest()->getQuery()->toArray();
            if ($this->isButtonPressed('yes')) {
                $this->removeDocument($this->params('doc'));
                $taskId = $queryParams['taskId'] ?? null;
                if (!is_null($taskId)) {
                    $this->closeTask($taskId);
                }
                return $this->handleRedirectToDocumentRoute(true);
            }

            return $this->redirect()->toRoute(
                null,
                ['action' => null],
                ['query' => $queryParams],
                true
            );
        }

        $form = $this->formHelper
            ->createFormWithRequest('ConfirmYesNo', $this->getRequest());

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('label', 'Are you sure you want to abort the letter generation?');
        $view->setTemplate('pages/confirm');

        return $this->renderView($view, 'Abort letter generation');
    }

    /**
     * Process Save Letter
     *
     * @param array $data data
     *
     * @return \Laminas\Http\Response | null
     */
    public function processSaveLetter($data)
    {
        $routeParams = $this->params()->fromRoute();

        $type = $routeParams['type'];

        $data = [
            'id' => $this->params('doc')
        ];

        // we need to link certain documents to multiple IDs
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getLicenceIdForApplication();
                break;

            case 'case':
                $data = array_merge($data, $this->getCaseData());
                break;

            case 'busReg':
                $data['licence'] = $this->params('licence');
                break;

            case 'irhpApplication':
                $data['licence'] = $this->params('licence');
                break;

            default:
                break;
        }

        $data[$type] = $routeParams[$this->getRouteParamKeyForType($type)];

        // if both the entityType and the entityId has some values then add it into $data
        if (!empty($routeParams['entityType']) && !empty($routeParams['entityId'])) {
            $data[$routeParams['entityType']] = $routeParams['entityId'];
        }

        // Update Document Record
        $dto = TransferCmd\Document\UpdateDocumentLinks::create($data);
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addUnknownError();
            $this->redirect = $this->redirect()->refresh();
            return;
        }

        return $this->redirect()->toRoute(
            null,
            ['action' => 'print'],
            ['query' => $this->getRequest()->getQuery()->toArray()],
            true
        );
    }

    /**
     * handle Redirect To Document Route
     *
     * @param bool $ajax ajax
     *
     * @return \Laminas\Http\Response
     */
    protected function handleRedirectToDocumentRoute($ajax = false)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        return $this->redirectToDocumentRoute($type, null, $routeParams, $ajax);
    }

    private function modifyFormForProposeToRevoke(\Common\Form\Form $form): void
    {
        $this->formHelper->remove($form, 'form-actions->email');
        $form->get('form-actions')->get('printAndPost')->removeAttribute('disabled');
        $form->get('form-actions')->get('printAndPost')->setLabel('Propose to revoke');
        $form->get('form-actions')->get('printAndPost')->setName('proposeToRevoke');
    }

    private function isProposeToRevoke(): bool
    {
        $docData = $this->fetchDocData();

        return $docData["details"]["category"] === Category::CATEGORY_COMPLIANCE
            && $docData["details"]["documentSubCategory"] === Category::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION;
    }

    private function processProposeToRevoke(): Response
    {
        $documentData = $this->fetchDocData();

        $proposeToRevokeCmdData = [
            'licence' => $this->getLicenceIdFromRouteParams(),
            'document' => $documentData['data']['id']
        ];

        $response = $this->handleCommand(
            TransferCmd\Licence\ProposeToRevoke::create($proposeToRevokeCmdData)
        );

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage(self::PRINT_MSGS_SUCCESS['proposeToRevoke']);
        } else {
            $this->flashMessengerHelper->addUnknownError();
        }

        return $this->handleRedirectToDocumentRoute(true);
    }

    private function getLicenceIdFromRouteParams(): int
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        if ($type === 'application') {
            return $this->getLicenceIdForApplication();
        }
        return $this->params('licence');
    }
}
