<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Category;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation as ByConversationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation as ByOrganisationQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Form\Model\Form\Message\Reply as ReplyForm;
use Olcs\Form\Model\Form\Message\Create as CreateForm;
use Olcs\Service\Data\MessagingAppOrLicNo;

class ConversationsController extends AbstractController implements ToggleAwareInterface
{
    use Lva\Traits\ExternalControllerTrait;

    protected $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory,
        protected FormHelperService $formHelperService,
        protected Navigation $navigationService,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    #[\Override]
    public function indexAction(): ViewModel
    {
        $params = [
            'page'         => $this->params()->fromQuery('page', 1),
            'limit'        => $this->params()->fromQuery('limit', 10),
            'sort'         => $this->params()->fromQuery('sort', 'd.issuedDate'),
            'order'        => $this->params()->fromQuery('order', 'DESC'),
            'organisation' => $this->getCurrentOrganisationId(),
            'query'        => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(ByOrganisationQuery::create($params));

        if ($response->isOk()) {
            $messages = $response->getResult();
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            $messages = [];
        }

        $table = $this->tableFactory
            ->buildTable('messages', $messages, $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('messages');

        return $view;
    }

    /**
     * @throws \Exception
     *
     * @return Response|ViewModel
     */
    public function addAction()
    {
        $form = $this->formHelperService->createForm(CreateForm::class, true, false);
        $form->get('correlationId')->setValue(sha1(microtime()));

        $fileFieldset = $form->get('form-actions')->get('file');
        $fileFieldset->setAttribute('class', $fileFieldset->getAttribute('class') . ' last');

        $isPost = $this->getRequest()->isPost();
        $canUploadFiles = $this->getCurrentOrganisation()['isMessagingFileUploadEnabled'];
        if (!$canUploadFiles) {
            $form->get('form-actions')->remove('file');
        }

        if ($isPost) {
            $form->setData($this->getRequest()->getPost());
        }

        $hasProcessedFiles = false;
        if ($canUploadFiles && $isPost) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'form-actions->file',
                $this->processFileUpload(...),
                $this->deleteFile(...),
                $this->getUploadedFiles(...),
                'form-actions->file->fileCount',
            );
        }

        if (!$hasProcessedFiles && $isPost && $form->isValid()) {
            return $this->submitConversation($form);
        }

        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('backUrl', $this->url()->fromRoute('conversations'));
        $view->setTemplate('messages-new');

        return $view;
    }

    /**
     * @return Response|ViewModel
     * @throws \Exception
     */
    private function submitConversation(\Laminas\Form\Form $form)
    {
        $response = $this->handleCommand($this->mapFormDataToCommand($form));
        if (!$response->isOk()) {
            $this->flashMessengerHelper->addErrorMessage(
                'There was an server error when submitting your conversation; please try later',
            );
            return $this->addAction();
        }

        $conversationId = $response->getResult()['id']['conversation'] ?? null;
        if (empty($conversationId)) {
            $this->flashMessengerHelper->addErrorMessage(
                'There was an server error when submitting your conversation; please try later',
            );
            return $this->addAction();
        }

        $this->flashMessengerHelper->addSuccessMessage('Conversation was created successfully');
        return $this->redirect()->toRoute('conversations/view', ['conversationId' => $conversationId]);
    }

    /**
     * @throws \Exception
     */
    private function mapFormDataToCommand(\Laminas\Form\Form $form): Create
    {
        $data = $form->getData();
        $processedData = [
            'messageSubject' => $data['form-actions']['inputs']['messageSubject'],
            'messageContent' => $data['form-actions']['inputs']['messageContent'],
            'correlationId'  => $data['correlationId'],
        ];

        $appOrLicNoPrefix = substr((string) $data['form-actions']['inputs']['appOrLicNo'], 0, 1);
        $appOrLicNoSuffix = substr((string) $data['form-actions']['inputs']['appOrLicNo'], 1);
        switch ($appOrLicNoPrefix) {
            case MessagingAppOrLicNo::PREFIX_LICENCE:
                $processedData['licence'] = $appOrLicNoSuffix;
                break;
            case MessagingAppOrLicNo::PREFIX_APPLICATION:
                $processedData['application'] = $appOrLicNoSuffix;
                break;
            default:
                throw new \Exception('Invalid prefix on appOrLicNo');
        }

        return Create::create($processedData);
    }

    /** @return ViewModel|Response */
    public function viewAction()
    {
        $this->navigationService->findBy('id', 'dashboard-messaging')->setActive();

        $params = [
            'page'         => $this->params()->fromQuery('page', 1),
            'limit'        => $this->params()->fromQuery('limit', 10),
            'conversation' => $this->params()->fromRoute('conversationId'),
            'query'        => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(ByConversationQuery::create($params));
        $canReply = false;
        $subject = '';

        if ($response->isOk()) {
            $messages = $response->getResult();
            $canReply = !$messages['extra']['conversation']['isClosed'];
            $subject = $this->getSubject($messages);
            unset($messages['extra']);
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            $messages = [];
        }

        $form = $this->formHelperService->createForm(ReplyForm::class, true, false);
        $form->get('correlationId')->setValue(sha1(microtime()));
        $this->formHelperService->setFormActionFromRequest($form, $this->getRequest());

        $table = $this->tableFactory->buildTable('messages-view', $messages, $params);

        $form->get('form-actions')->get('actions')->remove('guidance');

        $canUploadFiles = $this->getCurrentOrganisation()['isMessagingFileUploadEnabled'];
        if (!$canUploadFiles) {
            $form->get('form-actions')->remove('file');
        }

        $view = new ViewModel(
            [
                'table'          => $table,
                'form'           => $form,
                'canReply'       => $canReply,
                'openReply'      => false,
                'canUploadFiles' => $canUploadFiles,
                'subject'        => $subject,
                'backUrl'        => $this->url()->fromRoute('conversations'),
            ],
        );
        $view->setTemplate('messages-view');

        if ($this->getRequest()->isPost()) {
            return $this->parseReply($view, $form);
        }

        return $view;
    }

    /** @return Response|ViewModel */
    protected function parseReply(ViewModel $view, Form $form)
    {
        $form->setData((array)$this->getRequest()->getPost());
        $form->get('id')->setValue($this->params()->fromRoute('conversation'));

        $hasProcessedFiles = false;
        if ($this->getCurrentOrganisation()['isMessagingFileUploadEnabled']) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'form-actions->file',
                $this->processFileUpload(...),
                $this->deleteFile(...),
                $this->getUploadedFiles(...),
                'form-actions->file->fileCount',
            );

            $view->setVariable('openReply', $hasProcessedFiles);
        }

        if ($hasProcessedFiles || $this->params()->fromPost('action') !== 'reply' || !$form->isValid()) {
            return $view;
        }

        $response = $this->handleCommand(
            CreateMessageCommand::create(
                [
                    'conversation'   => $this->params()->fromRoute('conversationId'),
                    'correlationId'  => $this->getRequest()->getPost('correlationId'),
                    'messageContent' => $form->get('form-actions')->get('inputs')->get('reply')->getValue(),
                ],
            ),
        );

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage('Reply submitted successfully');
            return $this->redirect()->toRoute('conversations/view', $this->params()->fromRoute());
        }

        $this->handleErrors($response->getResult());

        return parent::indexAction();
    }

    public function processFileUpload($file): void
    {
        $dtoData = [
            'category'              => Category::CATEGORY_LICENSING,
            'subCategory'           => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'description'           => $file['name'],
            'isExternal'            => true,
            'messagingConversation' => $this->params()->fromRoute('conversationId'),
            'correlationId'         => $this->getRequest()->getPost('correlationId'),
        ];

        $this->uploadFile($file, $dtoData);
    }

    public function getUploadedFiles()
    {
        $params = [
            'category'      => Category::CATEGORY_LICENSING,
            'subCategory'   => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'correlationId' => $this->getRequest()->getPost('correlationId'),
        ];

        $response = $this->handleQuery(Documents::create($params));

        return $response->getResult();
    }

    private function getSubject(array $messageData): string
    {
        $subject = $messageData['extra']['licence']['licNo'];
        if (isset($messageData['extra']['application']['id'])) {
            $subject .= ' / ' . $messageData['extra']['application']['id'];
        }
        return $subject . ': ' . $messageData['extra']['conversation']['subject'];
    }
}
