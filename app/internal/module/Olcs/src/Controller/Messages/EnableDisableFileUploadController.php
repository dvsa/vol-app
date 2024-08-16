<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Form\Form;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\Messaging\EnableFileUpload as EnableCommand;
use Dvsa\Olcs\Transfer\Command\Messaging\DisableFileUpload as DisableCommand;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Data\Mapper\Task;
use Olcs\Form\Model\Form\DisableFileUploadPopup;
use Olcs\Form\Model\Form\EnableFileUploadPopup;
use RuntimeException;

class EnableDisableFileUploadController extends ApplicationController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];

    protected function renderForm(Form $form, AbstractCommand $command, string $title, string $message)
    {
        if ($this->getRequest()->isPost()) {
            $response = $this->handleCommand($command);

            if ($response->isOk()) {
                $this->flashMessengerHelper->addSuccessMessage($message);

                return $this->refresh();
            } elseif ($response->isClientError()) {
                Task::mapFormErrors($response->getResult()['messages'], $form, $this->flashMessengerHelper);
            } else {
                $this->flashMessengerHelper->addUnknownError();
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->render($view, $title);
    }

    public function enableAction(): ViewModel
    {
        $form = $this->getForm(EnableFileUploadPopup::class);
        $command = EnableCommand::create(['organisation' => $this->getOrganisationId()]);
        $message = 'File upload enabled';
        $title = 'Enable File Upload';

        return $this->renderForm($form, $command, $title, $message);
    }

    public function disableAction(): ViewModel
    {
        $form = $this->getForm(DisableFileUploadPopup::class);
        $command = DisableCommand::create(['organisation' => $this->getOrganisationId()]);
        $message = 'File upload disabled';
        $title = 'Disable File Upload';

        return $this->renderForm($form, $command, $title, $message);
    }

    protected function getOrganisationId(): int
    {
        if ($licenceId = $this->params()->fromRoute('licence')) {
            return $this->handleQuery(
                Licence::create(['id' => $licenceId]),
            )->getResult()['organisation']['id'];
        }

        if ($applicationId = $this->params()->fromRoute('application')) {
            return $this->handleQuery(
                Application::create(['id' => $applicationId]),
            )->getResult()['licence']['organisation']['id'];
        }

        throw new RuntimeException('Error: licence or application required');
    }

    public function refresh(): ViewModel
    {
        $view = new ViewModel([]);
        $view->setTemplate('pages/reload');
        $view->setCaptureTo('');
        $view->setTerminal(true);
        return $view;
    }
}
