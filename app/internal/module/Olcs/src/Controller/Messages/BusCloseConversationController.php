<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Laminas\Http\Response;

class BusCloseConversationController extends AbstractCloseConversationController
{
    protected function getRedirect(): Response
    {
        $params = [
            'busRegId' => $this->params()->fromRoute('busRegId'),
            'licence' => $this->params()->fromRoute('licence'),
            'action'  => 'close',

        ];
        return $this->redirect()->toRouteAjax('licence/bus_conversation', $params);
    }
}
