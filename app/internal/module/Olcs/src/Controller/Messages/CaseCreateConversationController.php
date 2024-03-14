<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Cases\Cases;
use Exception;
use Laminas\Mvc\MvcEvent;
use Olcs\Controller\Interfaces\CaseControllerInterface;

class CaseCreateConversationController extends AbstractCreateConversationController implements CaseControllerInterface
{
    protected $navigationId = 'case_conversations';

    protected $redirectConfig = [
        'add' => [
            'route' => 'case_conversation/view',
            'resultIdMap' => [
                'case' => 'case',
                'conversation' => 'conversation',
                'licence' => 'licence'
            ],
            'reUseParams' => true
        ]
    ];

    public function onDispatch(MvcEvent $e)
    {
        if ($this->getRequest()->isPost()) {
            $caseId = $e->getRouteMatch()->getParam('case');
            $queryResponse = $this->handleQuery(Cases::create(['id' => $caseId]));
            if (!$queryResponse->isOk()) {
                throw new Exception(
                    sprintf(
                        'Unexpected error when loading case. Response: HTTP  %s :: %s',
                        $queryResponse->getStatusCode(),
                        $queryResponse->getBody(),
                    ),
                );
            }

            $queryResult = $queryResponse->getResult();
            $this->licence = (string)$queryResult['licence']['id'];
        }

        return parent::onDispatch($e);
    }
}
