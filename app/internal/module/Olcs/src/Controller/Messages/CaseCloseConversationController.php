<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Laminas\Http\Response;

class CaseCloseConversationController extends AbstractCloseConversationController
{
    protected function getRedirect(): Response
    {
        $params = [
            'case' => $this->params()->fromRoute('case'),
            'licence' => $this->params()->fromRoute('licence'),
            'action'  => 'close',

        ];
        return $this->redirect()->toRouteAjax('case_conversation', $params);
    }
}
