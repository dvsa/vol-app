<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Exception;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\BusRegControllerInterface;

class BusEnableDisableMessagingController extends AbstractEnableDisableMessagingController implements BusRegControllerInterface
{
    protected $navigationId = 'licence_bus';

    public function getLeftView(): ?ViewModel
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return null;
        }

        $view = new ViewModel(['navigationId' => 'bus_conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    protected function getRoutePrefix(): string
    {
        return 'bus_conversation';
    }

    protected function getOrganisationId(): int
    {
        $queryResponse = $this->handleQuery(Licence::create(['id' => $this->params()->fromRoute('licence')]));
        if (!$queryResponse->isOk()) {
            throw new Exception(
                sprintf(
                    'Unexpected error when querying licence for organisation ID. Response: HTTP  %s :: %s',
                    $queryResponse->getStatusCode(),
                    $queryResponse->getBody(),
                ),
            );
        }
        $queryResult = $queryResponse->getResult();

        return $queryResult['organisation']['id'];
    }
}
