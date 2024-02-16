<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\LicenceControllerInterface;

class LicenceCreateConversationController
    extends AbstractCreateConversationController
    implements LicenceControllerInterface
{
    protected $navigationId = 'conversations';

    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/conversation/view',
            'resultIdMap' => [
                'conversation' => 'conversation',
                'licence' => 'licence'
            ],
            'reUseParams' => true
        ]
    ];
}
