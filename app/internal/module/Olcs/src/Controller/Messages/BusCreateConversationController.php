<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\BusRegControllerInterface;

class BusCreateConversationController extends AbstractCreateConversationController implements BusRegControllerInterface
{
    protected $navigationId = 'bus_conversations';

    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/bus_conversation/view',
            'resultIdMap' => [
                'bus' => 'bus',
                'conversation' => 'conversation',
                'licence' => 'licence'
            ],
            'reUseParams' => true
        ]
    ];
}
