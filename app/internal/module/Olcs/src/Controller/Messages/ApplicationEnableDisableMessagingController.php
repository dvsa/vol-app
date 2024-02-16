<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Exception;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;

class ApplicationEnableDisableMessagingController
    extends AbstractEnableDisableMessagingController
    implements ApplicationControllerInterface
{
    protected $navigationId = 'application';

    public function getLeftView(): ?ViewModel
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return null;
        }

        $view = new ViewModel(['navigationId' => 'application_conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    protected function getRoutePrefix(): string
    {
        return 'lva-application';
    }

    protected function getOrganisationId(): int
    {
        $queryResponse = $this->handleQuery(Application::create(['id' => $this->params()->fromRoute('application')]));
        if (!$queryResponse->isOk()) {
            throw new Exception(
                sprintf(
                    'Unexpected error when querying application for organisation ID. Response: HTTP  %s :: %s',
                    $queryResponse->getStatusCode(),
                    $queryResponse->getBody(),
                )
            );
        }
        $queryResult = $queryResponse->getResult();

        return $queryResult['licence']['organisation']['id'];
    }
}
