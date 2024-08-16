<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

class IrhpApplicationCreateConversationController extends AbstractCreateConversationController implements IrhpApplicationControllerInterface
{
    protected $navigationId = 'licence_irhp_permits-application';

    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/irhp-application-conversation/view',
            'resultIdMap' => [
                'conversation' => 'conversation',
                'licence' => 'licence'
            ],
            'reUseParams' => true
        ]
    ];

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(['navigationId' => 'irhp_conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }
}
