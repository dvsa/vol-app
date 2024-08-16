<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Cases\Cases;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Exception;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\CaseControllerInterface;

class CaseEnableDisableMessagingController extends AbstractEnableDisableMessagingController implements CaseControllerInterface
{
    protected $navigationId = 'case';

    public function getLeftView(): ?ViewModel
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return null;
        }

        $view = new ViewModel(['navigationId' => 'case_conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    protected function getRoutePrefix(): string
    {
        return 'case_conversation';
    }

    protected function getOrganisationId(): int
    {
        $queryResponse = $this->handleQuery(Cases::create(['id' => $this->params()->fromRoute('case')]));
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

        return $queryResult['licence']['organisation']['id'];
    }
}
