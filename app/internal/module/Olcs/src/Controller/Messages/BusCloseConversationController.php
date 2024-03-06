<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Laminas\Http\Response;

class BusCloseConversationController extends AbstractCloseConversationController
{
    protected function getRedirect(): Response
    {
        $params = [
            'bus' => $this->params()->fromRoute('bus'),
            'licence' => $this->params()->fromRoute('licence'),
            'action'  => 'close',

        ];
        return $this->redirect()->toRouteAjax('bus_conversation', $params);
    }
}
