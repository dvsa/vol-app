<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;

class ApplicationCreateConversationController
    extends AbstractCreateConversationController
    implements ApplicationControllerInterface
{
    protected $navigationId = 'application_conversations';

    protected $redirectConfig = [
        'add' => [
            'route' => 'lva-application/conversation/view',
            'resultIdMap' => [
                'application' => 'application',
                'conversation' => 'conversation',
            ],
            'reUseParams' => true
        ]
    ];
}
