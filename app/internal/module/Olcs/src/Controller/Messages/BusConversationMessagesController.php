<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\BusRegControllerInterface;

class BusConversationMessagesController extends AbstractConversationMessagesController implements BusRegControllerInterface
{
    protected $topNavigationId = 'licence_bus';
    protected $navigationId = 'bus_conversations';
    protected $listVars = ['licence', 'conversation'];

    protected function getConversationViewRoute(): string
    {
        return 'licence/bus_conversation/view';
    }
}
