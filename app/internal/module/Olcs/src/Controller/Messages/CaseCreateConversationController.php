<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

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
}
