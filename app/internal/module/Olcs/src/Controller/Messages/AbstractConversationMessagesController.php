<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\Interfaces\MessagingControllerInterface;
use Olcs\Controller\Interfaces\NavigationIdProvider;
use Olcs\Form\Model\Form\LicenceMessageActions;
use Olcs\Form\Model\Form\LicenceMessageReply;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;

abstract class AbstractConversationMessagesController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface, NavigationIdProvider, MessagingControllerInterface
{
    use MessageAttachmentsTrait;

    protected $listDto = ByConversation::class;
    protected $topNavigationId = '';
    protected $tableName = 'messages-list';
    protected $tableViewTemplate = 'pages/conversation/messages';
    protected $routeIdentifier = 'messages';
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::MESSAGING
        ],
    ];

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        protected ScriptFactory $scriptFactory,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }

    abstract protected function getConversationViewRoute(): string;

    /** @param array $parameters */
    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['includeReadRoles'] = 1;
        $parameters['readRoles'] = [
            RefData::ROLE_OPERATOR_TC,
            RefData::ROLE_OPERATOR_ADMIN,
            RefData::ROLE_OPERATOR_TM,
            RefData::ROLE_OPERATOR_USER,
        ];

        return $parameters;
    }

    /**
     * @return array|Response|ViewModel|void
     */
    #[\Override]
    public function indexAction()
    {
        $this->scriptFactory->loadFiles(['table-actions']);

        $paramProvider = (new GenericList($this->listVars, $this->defaultTableSortField, $this->defaultTableOrderField))
            ->setDefaultLimit($this->defaultTableLimit);
        $paramProvider->setParams($this->plugin('params'));
        $providedParameters = $this->modifyListQueryParameters($paramProvider->provideParameters());
        $response = $this->handleQuery($this->listDto::create($providedParameters));

        $replyForm = $this->getForm(LicenceMessageReply::class);
        $replyForm->get('id')->setValue($this->params()->fromRoute('conversation'));
        $replyForm->get('correlationId')->setValue($this->newCorrelationId());
        $this->placeholder()->setPlaceholder('send-reply', $replyForm);

        $actionsForm = $this->getForm(LicenceMessageActions::class);
        $actionsForm->get('id')->setValue($this->params()->fromRoute('conversation'));

        $canReply = !$response->getResult()['extra']['conversation']['isClosed'];
        $this->placeholder()->setPlaceholder('can-reply', $canReply);
        if (!$canReply) {
            $actionsForm->get('form-actions')->get('close')->setAttribute('disabled', 'disabled');
            $actionsForm->get('form-actions')->get('close')->setAttribute(
                'class',
                'govuk-button govuk-button--warning govuk-button--disabled'
            );
        }

        $this->placeholder()->setPlaceholder('message-actions', $actionsForm);

        if (!$this->getRequest()->isPost()) {
            return parent::indexAction();
        }

        return $this->handlePost($replyForm, $canReply);
    }

    /** @return Response|ViewModel */
    protected function handlePost(Form $replyForm, bool $canReply)
    {
        if (strtolower((string) $this->params()->fromPost('action')) === 'end and archive conversation') {
            $route = str_replace('/view', '/close', $this->getConversationViewRoute());
            return $this->redirect()->toRoute($route, [], [], true);
        }

        return $this->parseReply($replyForm, $canReply);
    }

    /** @return Response|ViewModel */
    protected function parseReply(Form $form, bool $canReply)
    {
        $form->setData((array)$this->params()->fromPost());
        $form->get('id')->setValue($this->params()->fromRoute('conversation'));
        $form->get('correlationId')->setValue($this->getPostedCorrelationId() ?? $this->newCorrelationId());

        if (
            ($canReply && $this->handleAttachmentPost($form, 'form-actions->file'))
            || $this->params()->fromPost('action') !== 'reply'
            || !$form->isValid()
        ) {
            $this->placeholder()->setPlaceholder('open-reply', true);
            return $this->renderConversation();
        }

        $response = $this->handleCommand(CreateMessageCommand::create([
            'conversation' => $this->params()->fromRoute('conversation'),
            'messageContent' => $form->get('form-actions')->get('reply')->getValue(),
            'correlationId' => $this->getPostedCorrelationId(),
        ]));

        if ($response->isOk()) {
            $this->flashMessengerHelperService->addSuccessMessage('Reply submitted successfully');
            return $this->redirect()->toRoute($this->getConversationViewRoute(), $this->params()->fromRoute());
        }

        $this->handleErrors($response->getResult());

        return $this->renderConversation();
    }

    /** @return Response|ViewModel */
    protected function renderConversation()
    {
        return parent::indexAction();
    }

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(['navigationId' => $this->navigationId]);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    #[\Override]
    public function getNavigationId()
    {
        return $this->topNavigationId;
    }
}
