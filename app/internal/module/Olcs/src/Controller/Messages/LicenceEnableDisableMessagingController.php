<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Exception;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

class LicenceEnableDisableMessagingController
    extends AbstractEnableDisableMessagingController
    implements LicenceControllerInterface
{
    protected $navigationId = 'licence';

    public function getLeftView(): ?ViewModel
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return null;
        }

        $view = new ViewModel(['navigationId' => 'conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    protected function getRoutePrefix(): string
    {
        return 'licence';
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
                )
            );
        }
        $queryResult = $queryResponse->getResult();

        return $queryResult['organisation']['id'];
    }
}
