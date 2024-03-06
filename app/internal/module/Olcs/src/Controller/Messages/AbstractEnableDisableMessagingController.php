<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Disable as DisableCommand;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Enable as EnableCommand;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\MessagingControllerInterface;
use Olcs\Controller\Interfaces\NavigationIdProvider;
use Olcs\Data\Mapper\Task;
use Olcs\Form\Model\Form\DisableConversations;
use Olcs\Form\Model\Form\DisableConversationsPopup;
use Olcs\Form\Model\Form\EnableConversations;
use Olcs\Form\Model\Form\EnableConversationsPopup;

abstract class AbstractEnableDisableMessagingController extends ApplicationController implements LeftViewProvider, ToggleAwareInterface, NavigationIdProvider, MessagingControllerInterface
{
    protected $navigationId = 'conversations';
    protected $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];

    abstract protected function getRoutePrefix(): string;
    abstract protected function getOrganisationId(): int;

    public function indexAction()
    {
        $this->scriptFactory->loadFiles(['table-actions']);

        $action = $this->params()->fromRoute('type');

        if ($this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(
                $this->getRoutePrefix() . '/' . $action . '/popup',
                $this->params()->fromRoute(),
            );
        }

        if ($action === 'enable') {
            $title = 'Enable messaging';
            $form = $this->getForm(EnableConversations::class);
        } else {
            $title = 'Disable messaging';
            $form = $this->getForm(DisableConversations::class);
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->render($view, $title);
    }

    /** @return ViewModel|Response */
    public function popupAction()
    {
        $organisationId = $this->getOrganisationId();
        $action = $this->params()->fromRoute('type');

        if ($action === 'enable') {
            $title = 'Enable messaging';
            $form = $this->getForm(EnableConversationsPopup::class);
        } else {
            $title = 'Disable messaging';
            $form = $this->getForm(DisableConversationsPopup::class);
        }

        if ($this->getRequest()->isPost()) {
            if ($action === 'enable') {
                $message = 'messaging-enabled-success';
                $commandResponse = $this->handleCommand(
                    EnableCommand::create(['organisation' => $organisationId]),
                );
            } else {
                $message = 'messaging-disabled-success';
                $commandResponse = $this->handleCommand(
                    DisableCommand::create(['organisation' => $organisationId]),
                );
            }

            if ($commandResponse->isOk()) {
                $this->flashMessengerHelper->addSuccessMessage($message);

                return $this->redirect()->toRouteAjax(
                    $this->getRoutePrefix(),
                    $this->params()->fromRoute(),
                );
            } elseif ($commandResponse->isClientError()) {
                Task::mapFormErrors($commandResponse->getResult()['messages'], $form, $this->flashMessengerHelper);
            } else {
                $this->flashMessengerHelper->addUnknownError();
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->render($view, $title);
    }

    public function getNavigationId()
    {
        return $this->navigationId;
    }
}
