<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Messaging\Close;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Olcs\Data\Mapper\Task;
use Olcs\Form\Model\Form\CloseConversation;

class LicenceCloseConversationController extends AbstractController implements ToggleAwareInterface
{
    protected array $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];
    private FlashMessengerHelperService $flashMessengerHelperService;

    public function __construct(
        ScriptFactory               $scriptFactory,
        FormHelperService           $formHelper,
        TableFactory                $tableFactory,
        HelperPluginManager         $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelperService
    )
    {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager);

        $this->flashMessengerHelperService = $flashMessengerHelperService;
    }

    /**
     * @return ViewModel|Response
     */
    public function confirmAction()
    {
        $form = $this->getForm(CloseConversation::class);
        $form->get('id')->setValue($this->params()->fromRoute('conversation'));

        if ($this->getRequest()->isPost()) {
            $closeCommand = Close::create(['id' => $this->params()->fromRoute('conversation')]);
            $response = $this->handleCommand($closeCommand);

            if ($response->isOk()) {
                $this->flashMessengerHelperService->addSuccessMessage('conversation-closed-success');

                $params = [
                    'licence' => $this->params()->fromRoute('licence'),
                    'action' => 'close',
                ];
                return $this->redirect()->toRouteAjax('licence/conversation', $params);
            } else if ($response->isClientError()) {
                Task::mapFormErrors($response->getResult()['messages'], $form, $this->flashMessengerHelperService);
            } else {
                $this->flashMessengerHelperService->addUnknownError();
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'End Conversation');
    }
}
